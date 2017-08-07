<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Braintree Settlement Report controller
 * @since 2.1.0
 */
class Index extends Action
{
    /**
     * @var PageFactory
     * @since 2.1.0
     */
    protected $resultPageFactory;

    const ADMIN_RESOURCE = 'Magento_Braintree::settlement_report';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return Page
     * @since 2.1.0
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::ADMIN_RESOURCE);
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__('Braintree Settlement Report'));

        return $resultPage;
    }
}
