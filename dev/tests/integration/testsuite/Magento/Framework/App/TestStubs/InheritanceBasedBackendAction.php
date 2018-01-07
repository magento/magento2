<?php declare(strict_types=1);

namespace Magento\Framework\App\TestStubs;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class InheritanceBasedBackendAction extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(Action\Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        return $this->pageFactory->create();
    }
}
