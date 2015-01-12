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
     * Retrieve validation result as JSON
     *
     * @return void
     */
    public function execute()
    {
        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');

        $result = $this->_validate();
        $valid = $result->getIsValid();
        $success = $result->getRequestSuccess();
        // ID of the store where order is placed
        $storeId = $this->getRequest()->getParam('store_id');
        // Sanitize value if needed
        if (!is_null($storeId)) {
            $storeId = (int)$storeId;
        }

        $groupId = $this->_objectManager->get(
            'Magento\Customer\Model\Vat'
        )->getCustomerGroupIdBasedOnVatNumber(
            $this->getRequest()->getParam('country'),
            $result,
            $storeId
        );

        $body = $coreHelper->jsonEncode(['valid' => $valid, 'group' => $groupId, 'success' => $success]);
        $this->getResponse()->representJson($body);
    }
}
