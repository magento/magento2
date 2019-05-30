<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Sales\Helper\Guest as GuestHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;

class View extends Action\Action
{
    /**
     * @var \Magento\Sales\Helper\Guest
     */
    protected $guestHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param GuestHelper $guestHelper
     * @param PageFactory $resultPageFactory
     * @param Validator|null $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        GuestHelper $guestHelper,
        PageFactory $resultPageFactory,
        Validator $formKeyValidator = null
    ) {
        $this->guestHelper = $guestHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath('*/*/form/');
            }
        }

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
