<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Variable\Controller\Adminhtml\System\Variable;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\Variable as ModelVariable;

/**
 * Retrieve variables list for WYSIWYG
 *
 * @api
 * @since 100.0.2
 */
class WysiwygPlugin extends Variable implements HttpGetActionInterface
{
    /**
     * WYSIWYG Plugin Action
     *
     * @return Json
     */
    public function execute()
    {
        $customVariables = $this->_objectManager->create(ModelVariable::class)
            ->getVariablesOptionArray(true);
        $storeContactVariables = $this->_objectManager->create(
            Variables::class
        )->toOptionArray(
            true
        );
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([$storeContactVariables, $customVariables]);
    }
}
