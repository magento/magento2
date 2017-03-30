<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\System\Config\Validatevat;

class ValidateAdvanced extends \Magento\Customer\Controller\Adminhtml\System\Config\Validatevat
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
     * Retrieve validation result as JSON
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_validate();
        $valid = $result->getIsValid();
        $success = $result->getRequestSuccess();
        // ID of the store where order is placed
        $storeId = $this->getRequest()->getParam('store_id');
        // Sanitize value if needed
        if ($storeId !== null) {
            $storeId = (int)$storeId;
        }

        $groupId = $this->_objectManager->get(\Magento\Customer\Model\Vat::class)
            ->getCustomerGroupIdBasedOnVatNumber(
                $this->getRequest()->getParam('country'),
                $result,
                $storeId
            );

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['valid' => $valid, 'group' => $groupId, 'success' => $success]);
    }
}
