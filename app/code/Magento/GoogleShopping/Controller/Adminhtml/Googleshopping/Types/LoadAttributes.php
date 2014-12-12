<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class LoadAttributes extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Get Google Content attributes list
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->getResponse()->setBody(
                $this->_view->getLayout()->createBlock(
                    'Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Attributes'
                )->setAttributeSetId(
                    $this->getRequest()->getParam('attribute_set_id')
                )->setTargetCountry(
                    $this->getRequest()->getParam('target_country')
                )->setAttributeSetSelected(
                    true
                )->toHtml()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            // just need to output text with error
            $this->messageManager->addError(__("We can't load attributes."));
        }
    }
}
