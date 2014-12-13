<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

class DeniedJson extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * @var \Magento\Framework\Controller\Result\JSONFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory
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
     * @return \Magento\Framework\Controller\Result\JSON
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\JSON $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($this->_getDeniedJson());
    }
}
