<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo;

use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class Email
 *
 * @package Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo
 */
class Email extends \Magento\Backend\App\Action
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if (!$creditmemoId) {
            return;
        }
        $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        if (!$creditmemo) {
            return;
        }
        $this->_objectManager->create('Magento\Sales\Model\Order\CreditmemoNotifier')
            ->notify($creditmemo);

        $this->messageManager->addSuccess(__('You sent the message.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);
        return $resultRedirect;
    }
}
