<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class \Magento\Customer\Controller\Account\LogoutSuccess
 *
 * @since 2.0.0
 */
class LogoutSuccess extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Logout success page
     *
     * @return \Magento\Framework\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
