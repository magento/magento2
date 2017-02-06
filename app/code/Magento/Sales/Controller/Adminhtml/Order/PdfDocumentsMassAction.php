<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;

abstract class PdfDocumentsMassAction extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->getOrderCollection()->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Get Order Collection Factory
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     * @deprecated
     */
    private function getOrderCollection()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class
            );
        }
        return $this->orderCollectionFactory;
    }
}
