<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureProductToAdd
 *
 * @since 2.0.0
 */
class ConfigureProductToAdd extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Ajax handler to response configuration fieldset of composite product in order
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        // Prepare data
        $productId = (int)$this->getRequest()->getParam('id');

        $configureResult = new \Magento\Framework\DataObject();
        $configureResult->setOk(true);
        $configureResult->setProductId($productId);
        $sessionQuote = $this->_objectManager->get(\Magento\Backend\Model\Session\Quote::class);
        $configureResult->setCurrentStoreId($sessionQuote->getStore()->getId());
        $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());

        // Render page
        /** @var \Magento\Catalog\Helper\Product\Composite $helper */
        $helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class);
        return $helper->renderConfigureResult($configureResult);
    }
}
