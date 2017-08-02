<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class \Magento\Sales\Controller\Guest\PrintCreditmemo
 *
 * @since 2.0.0
 */
class PrintCreditmemo extends \Magento\Sales\Controller\AbstractController\PrintCreditmemo
{
    /**
     * @var OrderLoader
     * @since 2.0.0
     */
    protected $orderLoader;

    /**
     * @var CreditmemoRepositoryInterface;
     * @since 2.0.0
     */
    protected $creditmemoRepository;

    /**
     * @param Context $context
     * @param OrderViewAuthorization $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param OrderLoader $orderLoader
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        OrderViewAuthorization $orderAuthorization,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        OrderLoader $orderLoader
    ) {
        $this->orderLoader = $orderLoader;
        $this->creditmemoRepository = $creditmemoRepository;
        parent::__construct(
            $context,
            $orderAuthorization,
            $registry,
            $resultPageFactory,
            $creditmemoRepository
        );
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        $creditmemoId = (int)$this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
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
