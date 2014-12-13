<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class LoadAttributeSets extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Get available attribute sets
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->getResponse()->setBody(
                $this->_view->getLayout()->getBlockSingleton(
                    'Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Form'
                )->getAttributeSetsSelectElement(
                    $this->getRequest()->getParam('target_country')
                )->toHtml()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            // just need to output text with error
            $this->messageManager->addError(__("We can't load attribute sets."));
        }
    }
}
