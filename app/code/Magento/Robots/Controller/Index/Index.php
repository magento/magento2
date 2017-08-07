<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Processes request to robots.txt file and returns robots.txt content as result
 * @since 2.2.0
 */
class Index extends Action
{
    /**
     * @var PageFactory
     * @since 2.2.0
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * Generates robots.txt data and returns it as result
     *
     * @return Page
     * @since 2.2.0
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create(true);
        $resultPage->addHandle('robots_index_index');
        return $resultPage;
    }
}
