<?php

namespace LumiDev\AutoRedirectToProduct\Observer;

use GuzzleHttp\Client;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class VerifyActivationKey implements ObserverInterface
{
    const XML_PATH_ACTIVATIONKEY = 'auto_redirect_toproduct/activation/key';

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var Client */
    protected $guzzleClient;

    /** @var \Magento\Framework\Encryption\EncryptorInterface */
    protected $encryptor;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $_request;

    /** @var \Magento\Config\Model\ResourceModel\Config */
    protected $_resourceConfig;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $jsonHelper;

    /** @var \Magento\Framework\App\Cache\TypeListInterface */
    protected $_cacheTypeList;

    /** @var \Magento\Framework\App\Cache\Frontend\Pool */
    protected $_cacheFrontendPool;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Client $guzzleClient,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->_scopeConfig       = $scopeConfig;
        $this->guzzleClient       = $guzzleClient;
        $this->encryptor          = $encryptor;
        $this->_cacheTypeList     = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_request           = $request;
        $this->jsonHelper         = $jsonHelper;
        $this->_resourceConfig    = $resourceConfig;
        $this->messageManager     = $messageManager;
        $this->logger             = $logger;
    }

    public function execute(Observer $observer)
    {

        file_put_contents(BP . '/var/log/observer_ping.log', date('c')." PING\n", FILE_APPEND);
        $key = $this->_scopeConfig->getValue(
            self::XML_PATH_ACTIVATIONKEY,
            ScopeInterface::SCOPE_STORE
        );
        $k = $this->encryptor->decrypt($key);

        if (!$key) {
            return; // rien à vérifier
        }
        $domain = $this->_request->getServer('HTTP_HOST');
        
        try {
            $response = $this->guzzleClient->request(
                'POST',
                'https://admin.obankinggo.com/licence/licence_checker.php',
                [
                    'headers' => [
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'body'    => json_encode([
                        'auth_key' => $k,
                        'domain' => $domain,
                        'module_name' => "auto_redirect_toproduct"
                    ]),
                    'timeout' => 10,
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            $this->logger->debug('API RAW BODY: ' . (string)$response->getBody());
            $this->logger->debug('Réponse API licence : ' . print_r($data, true));
        } catch (\Exception $e) {
            $this->logger->error('Erreur API licence : ' . $e->getMessage());

            throw new LocalizedException(
                __('Erreur lors de la validation de la licence : %1', $e->getMessage())
            );
        }
        var_dump($data);
	$success = isset($data['suc']) ? (int)$data['suc'] : 0;
	$msg     = $data['msg'] ?? 'Erreur inconnue';
        if ($success !== 1 ) {

           $this->_resourceConfig->saveConfig(self::XML_PATH_ACTIVATIONKEY, '', 'default', 0);
           $this->_resourceConfig->saveConfig('auto_redirect_toproduct/activation/enable', 0, 'default', 0);
        }

        $types = ['config'];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
