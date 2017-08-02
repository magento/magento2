<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\PdfDocumentsMassAction
 *
 * @since 2.1.3
 */
abstract class PdfDocumentsMassAction extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     * @since 2.1.3
     */
    protected $orderCollectionFactory;

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     * @since 2.1.3
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
     * @deprecated 2.1.3
     * @since 2.1.3
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
