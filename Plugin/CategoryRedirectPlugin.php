<?php
namespace LumiDev\AutoRedirectToProduct\Plugin;

use Magento\Catalog\Controller\Category\View as CategoryViewController;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
class CategoryRedirectPlugin
{
    protected $redirectFactory;
    protected $productCollectionFactory;
    private ScopeConfigInterface $scopeConfig;
    public function __construct(
        RedirectFactory $redirectFactory,
        CollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig=$scopeConfig;
    }

    public function aroundExecute(CategoryViewController $subject, \Closure $proceed)
    {


        $enabled = $this->scopeConfig->isSetFlag(
            'auto_redirect_toproduct/activation/enable',
            ScopeInterface::SCOPE_STORE
        );

        if (!$enabled) {
            return $proceed();
        }

        $categoryId = (int) $subject->getRequest()->getParam('id');
        if (!$categoryId) {
            return $proceed();
        }

        $collection = $this->productCollectionFactory->create()
            ->addCategoriesFilter(['eq' => $categoryId])
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
            ->setPageSize(2);

        if ($collection->getSize() === 1) {
            $product = $collection->getFirstItem();
            return $this->redirectFactory->create()->setUrl($product->getProductUrl());
        }

        return $proceed();
    }
}