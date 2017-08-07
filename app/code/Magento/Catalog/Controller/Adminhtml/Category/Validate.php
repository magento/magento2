<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

/**
 * Catalog category validate
 * @since 2.1.0
 */
class Validate extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.1.0
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * AJAX category validation action
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @since 2.1.0
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        
        return $resultJson;
    }
}
