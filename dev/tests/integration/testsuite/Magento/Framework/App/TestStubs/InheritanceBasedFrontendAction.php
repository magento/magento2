<?php declare(strict_types=1);

namespace Magento\Framework\App\TestStubs;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
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

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $this->executeWasCalled = true;
        return $this->pageFactory->create();
    }

    public function isExecuted(): bool
    {
        return $this->executeWasCalled;
    }
}
