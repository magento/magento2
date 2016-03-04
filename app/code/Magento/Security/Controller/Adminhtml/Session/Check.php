<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Controller\Adminhtml\Session;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Security\Helper\SecurityCookie;
use Magento\Security\Model\AdminSessionsManager;

/**
 * Ajax Admin session checker
 */
class Check extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var AdminSessionsManager
     */
    private $sessionsManager;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param AdminSessionsManager $sessionsManager
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        AdminSessionsManager $sessionsManager
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->sessionsManager = $sessionsManager;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        return $this->jsonFactory->create()->setData(
            [
                'isActive' => $this->sessionsManager->getCurrentSession()->isLoggedInStatus()
            ]
        );
    }
}
