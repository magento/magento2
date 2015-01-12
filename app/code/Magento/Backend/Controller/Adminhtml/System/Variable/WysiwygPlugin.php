<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\System\Variable;

class WysiwygPlugin extends \Magento\Backend\Controller\Adminhtml\System\Variable
{
    /**
     * WYSIWYG Plugin Action
     *
     * @return \Magento\Framework\Controller\Result\JSON
     */
    public function execute()
    {
        $objectManager = $this->_objectManager->create('Magento\Core\Model\Variable');
        $customVariables = $objectManager->getVariablesOptionArray(true);
        $storeContactVariabls = $this->_objectManager->create(
            'Magento\Email\Model\Source\Variables'
        )->toOptionArray(
            true
        );
        $translateVariables = $objectManager->getTranslateVariablesOptionArray(true);
        /** @var \Magento\Framework\Controller\Result\JSON $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([$storeContactVariabls, $customVariables, $translateVariables]);
    }
}
