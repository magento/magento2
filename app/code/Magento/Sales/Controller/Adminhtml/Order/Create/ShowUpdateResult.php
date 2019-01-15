<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

class ShowUpdateResult extends \Magento\Sales\Controller\Adminhtml\Order\Create implements HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        RawFactory $resultRawFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
    }

    /**
     * Show item update result from loadBlockAction
     * to prevent popup alert with resend data question
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $session = $this->_objectManager->get(\Magento\Backend\Model\Session::class);
        if ($session->hasUpdateResult() && is_scalar($session->getUpdateResult())) {
            $resultRaw->setContents($session->getUpdateResult());
        }
        $session->unsUpdateResult();
        return $resultRaw;
    }
}
