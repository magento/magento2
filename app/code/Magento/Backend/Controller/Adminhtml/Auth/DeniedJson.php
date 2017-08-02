<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Auth\DeniedJson
 *
 * @since 2.0.0
 */
class DeniedJson extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($this->_getDeniedJson());
    }
}
