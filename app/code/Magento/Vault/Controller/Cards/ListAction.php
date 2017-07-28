<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Vault\Controller\CardsManagement;

/**
 * Class \Magento\Vault\Controller\Cards\ListAction
 *
 * @since 2.1.0
 */
class ListAction extends CardsManagement
{
    /**
     * @var PageFactory
     * @since 2.1.0
     */
    private $pageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $pageFactory
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $pageFactory
    ) {
        parent::__construct($context, $customerSession);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @since 2.1.0
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Stored Payment Methods'));

        return $resultPage;
    }
}
