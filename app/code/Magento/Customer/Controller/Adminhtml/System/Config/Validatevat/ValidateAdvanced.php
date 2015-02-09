<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\System\Config\Validatevat;

class ValidateAdvanced extends \Magento\Customer\Controller\Adminhtml\System\Config\Validatevat
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
     * Retrieve validation result as JSON
     *
     * @return \Magento\Framework\Controller\Result\JSON
     */
    public function execute()
    {
        $result = $this->_validate();
        $valid = $result->getIsValid();
        $success = $result->getRequestSuccess();
        // ID of the store where order is placed
        $storeId = $this->getRequest()->getParam('store_id');
        // Sanitize value if needed
        if (!is_null($storeId)) {
            $storeId = (int)$storeId;
        }

        $groupId = $this->_objectManager->get('Magento\Customer\Model\Vat')
            ->getCustomerGroupIdBasedOnVatNumber(
                $this->getRequest()->getParam('country'),
                $result,
                $storeId
            );

        /** @var \Magento\Framework\Controller\Result\JSON $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['valid' => $valid, 'group' => $groupId, 'success' => $success]);
    }
}
