<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

/**
 * Retrieve variables list for WYSIWYG
 *
 * @api
 * @since 100.0.2
 */
class WysiwygPlugin extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * WYSIWYG Plugin Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $customVariables = $this->_objectManager->create(\Magento\Variable\Model\Variable::class)
            ->getVariablesOptionArray(true);
        $storeContactVariables = $this->_objectManager->create(
            \Magento\Variable\Model\Source\Variables::class
        )->toOptionArray(
            true
        );
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([$storeContactVariables, $customVariables]);
    }
}
