<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\TestStubs;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Stub interface only based frontend action controller for testing purposes.
 */
class InterfaceOnlyFrontendAction implements ActionInterface
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
     * @param PageFactory $pageFactory
     */
    public function __construct(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * Creates Page object
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $this->executeWasCalled = true;
        return $this->pageFactory->create();
    }

    /**
     * Returns whether `execute()` method was ran
     *
     * @return bool
     */
    public function isExecuted(): bool
    {
        return $this->executeWasCalled;
    }
}
