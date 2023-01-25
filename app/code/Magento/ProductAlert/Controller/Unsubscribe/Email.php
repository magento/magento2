<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductAlert\Controller\Unsubscribe;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\ProductAlert\Controller\Unsubscribe as UnsubscribeController;

/**
 * Unsubscribing from 'Back in stock Alert'.
 *
 * Is used to transform a Get request that triggered in the email into the Post request endpoint
 */
class Email extends UnsubscribeController implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $customerSession);
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
