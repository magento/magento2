<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductAlert\Controller\Unsubscribe;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Unsubscribing from 'Back in stock Alert'.
 *
 * Is used to transform a Get request that triggered in the email into the Post request endpoint
 */
class Email extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Processes the the request triggered in Unsubscription email related to 'back in stock alert'.
     *
     * @return Page
     */
    public function execute(): Page
    {
        $productId = (int)$this->getRequest()->getParam('product');
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        /** @var @va \Magento\Framework\View\Element\AbstractBlock $block */
        $block = $resultPage->getLayout()->getBlock('unsubscription_form');
        $block->setProductId($productId);
        return $resultPage;
    }
}
