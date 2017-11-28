<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Action\UrlBuilder;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic as CoreGeneric;

/**
 * Class CustomerView
 *
 * @package Magento\Catalog\Block\Adminhtml\Product\Edit\Button
 */
class CustomerView extends CoreGeneric
{
    /**
     * @var UrlBuilder
     */
    private $actionUrlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CustomerView constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param UrlBuilder $actionUrlBuilder
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        UrlBuilder $actionUrlBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->actionUrlBuilder = $actionUrlBuilder;

        parent::__construct($context, $registry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonData = [
            'label' => __('Customer View'),
            'on_click' => sprintf("window.open('%s', '_blank');", $this->getCustomerViewUrl()),
            'class' => 'action-secondary',
        ];

        $product = $this->getProduct();
        if (!$product->isSalable() || !$product->getId()) {
            $buttonData['disabled'] = 'disabled';
        }

        return $buttonData;
    }

    /**
     * @return string
     */
    private function getCustomerViewUrl()
    {
        /* @var \Magento\Store\Model\Store\Interceptor */
        $currentStore = $this->storeManager->getStore();

        return $this->actionUrlBuilder->getUrl(
            'catalog/product/view',
            $this->getProduct(),
            $currentStore->getStoreId(),
            $currentStore->getCode()
        );
    }
}
