<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class DeniedJson extends \Magento\Backend\Controller\Adminhtml\Auth implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Retrieve response for deniedJsonAction()
     *
     * @return array
     */
    protected function _getDeniedJson()
    {
        return [
            'ajaxExpired' => 1,
            'ajaxRedirect' => $this->_helper->getHomePageUrl()
        ];
    }

    /**
     * Denied JSON action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($this->_getDeniedJson());
    }
}
