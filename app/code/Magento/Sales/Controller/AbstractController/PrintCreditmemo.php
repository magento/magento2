<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class \Magento\Sales\Controller\AbstractController\PrintCreditmemo
 *
 * @since 2.0.0
 */
abstract class PrintCreditmemo extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderViewAuthorizationInterface
     * @since 2.0.0
     */
    protected $orderAuthorization;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @var CreditmemoRepositoryInterface
     * @since 2.0.0
     */
    protected $creditmemoRepository;

    /**
     * @param Context $context
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        OrderViewAuthorizationInterface $orderAuthorization,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->orderAuthorization = $orderAuthorization;
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        parent::__construct($context);
    }

    /**
     * Print Creditmemo Action
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $creditmemoId = (int)$this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            $order = $creditmemo->getOrder();
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        }

        if ($this->orderAuthorization->canView($order)) {
            $this->_coreRegistry->register('current_order', $order);
            if (isset($creditmemo)) {
                $this->_coreRegistry->register('current_creditmemo', $creditmemo);
            }
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('print');
            return $resultPage;
        } else {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->_objectManager->get(\Magento\Customer\Model\Session::class)->isLoggedIn()) {
                $resultRedirect->setPath('*/*/history');
            } else {
                $resultRedirect->setPath('sales/guest/form');
            }
            return $resultRedirect;
        }
    }
}
