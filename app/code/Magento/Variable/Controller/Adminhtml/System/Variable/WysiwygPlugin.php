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
 * @since 2.0.0
 */
class WysiwygPlugin extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * WYSIWYG Plugin Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @since 2.0.0
     */
    public function execute()
    {
        $customVariables = $this->_objectManager->create(\Magento\Variable\Model\Variable::class)
            ->getVariablesOptionArray(true);
        $storeContactVariabls = $this->_objectManager->create(
            \Magento\Email\Model\Source\Variables::class
        )->toOptionArray(
            true
        );
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([$storeContactVariabls, $customVariables]);
    }
}
