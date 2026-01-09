<?php
namespace LumiDev\AutoRedirectToProduct\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

class EnableDisable extends Field
{
    public const XML_PATH_ACTIVATION = 'auto_redirect_toproduct/activation/key';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResourceConfig
     */
    protected $resourceConfig;

    public function __construct(
        Context $context,
        ResourceConfig $resourceConfig,
        array $data = []
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->scopeConfig = $context->getScopeConfig();

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);

        // Récup config
        $key = $this->scopeConfig->getValue(
            self::XML_PATH_ACTIVATION,
            ScopeInterface::SCOPE_STORE
        );

        // Si clé vide => message d’erreur
        if ($key === null || $key === '') {
            $html = sprintf(
                '<p><strong class="required" style="color:red;">%s</strong></p>',
                __("Merci de mettre une clé d'activation valide !")
            );
        }

        return $html;
    }
}
