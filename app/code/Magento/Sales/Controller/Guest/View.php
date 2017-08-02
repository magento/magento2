<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action;
use Magento\Sales\Helper\Guest as GuestHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class \Magento\Sales\Controller\Guest\View
 *
 * @since 2.0.0
 */
class View extends Action\Action
{
    /**
     * @var \Magento\Sales\Helper\Guest
     * @since 2.0.0
     */
    protected $guestHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Helper\Guest $guestHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Action\Context $context,
        GuestHelper $guestHelper,
        PageFactory $resultPageFactory
    ) {
        $this->guestHelper = $guestHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $result = $this->guestHelper->loadValidOrder($this->getRequest());
        if ($result instanceof ResultInterface) {
            return $result;
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->guestHelper->getBreadcrumbs($resultPage);
        return $resultPage;
    }
}
