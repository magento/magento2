<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\TestStubs;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Stub inheritance based frontend action controller for testing purposes.
 */
class InheritanceBasedFrontendAction extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var bool
     */
    private $executeWasCalled = false;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Runs `execute()` method to create Page
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $this->executeWasCalled = true;
        return $this->pageFactory->create();
    }

    /**
     * Determines whether execute method was called
     *
     * @return bool
     */
    public function isExecuted(): bool
    {
        return $this->executeWasCalled;
    }
}
