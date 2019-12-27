<?php
namespace Smetana\Third\Controller\Adminhtml\Partner;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result;

/**
 * Partner grid Index Action
 *
 * @package Smetana\Third\Controller\Adminhtml\Partner
 */
class Index extends Action
{
    /**
     * Page Factory instance
     *
     * @var Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index Action execute
     *
     * @return Result\Page
     */
    public function execute(): Result\Page
    {
        /** @var Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Smetana_Third::partner');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Product Partners'));

        return  $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return  true;
    }
}
