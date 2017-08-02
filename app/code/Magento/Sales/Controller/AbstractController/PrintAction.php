<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class \Magento\Sales\Controller\AbstractController\PrintAction
 *
 * @since 2.0.0
 */
abstract class PrintAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     * @since 2.0.0
     */
    protected $orderLoader;

    /**
     * @var PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory
    ) {
        $this->orderLoader = $orderLoader;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Print Order Action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('print');
        return $resultPage;
    }
}
