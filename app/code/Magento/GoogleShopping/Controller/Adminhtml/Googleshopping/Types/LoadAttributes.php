<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            // just need to output text with error
            $this->messageManager->addError(__("We can't load attributes."));
        }
    }
}
