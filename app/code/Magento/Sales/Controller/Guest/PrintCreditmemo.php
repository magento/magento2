<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class PrintCreditmemo extends \Magento\Sales\Controller\AbstractController\PrintCreditmemo
{
    /**
     * @var OrderLoader
     */
    protected $orderLoader;

    /**
     * @param Context $context
     * @param OrderViewAuthorization $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param OrderLoader $orderLoader
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        OrderViewAuthorization $orderAuthorization,
        \Magento\Framework\Registry $registry,
        OrderLoader $orderLoader,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->orderLoader = $orderLoader;
        parent::__construct(
            $context,
            $orderAuthorization,
            $registry,
            $resultPageFactory,
            $resultRedirectFactory
        );
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\Result\Redirect) {
            return $result;
        }

        $creditmemoId = (int)$this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')->load($creditmemoId);
            $order = $creditmemo->getOrder();
        } else {
            $order = $this->_coreRegistry->registry('current_order');
        }

        if ($this->orderAuthorization->canView($order)) {
            if (isset($creditmemo)) {
                $this->_coreRegistry->register('current_creditmemo', $creditmemo);
            }
            return $this->resultPageFactory->create()->addHandle('print');
        } else {
            return $this->resultRedirectFactory->create()->setPath('sales/guest/form');
        }
    }
}
