<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Vault\Controller\CardsManagement;

class ListAction extends CardsManagement
{
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        private readonly PageFactory $pageFactory
    ) {
        parent::__construct($context, $customerSession);
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Stored Payment Methods'));

        return $resultPage;
    }
}
