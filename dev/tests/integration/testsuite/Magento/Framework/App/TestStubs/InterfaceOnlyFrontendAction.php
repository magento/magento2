<?php declare(strict_types=1);

namespace Magento\Framework\App\TestStubs;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\View\Result\PageFactory;

class InterfaceOnlyFrontendAction implements ActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        return $this->pageFactory->create();
    }
}
