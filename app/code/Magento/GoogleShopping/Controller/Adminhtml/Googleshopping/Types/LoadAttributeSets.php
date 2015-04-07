<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class LoadAttributeSets extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Get available attribute sets
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->getBlockSingleton('Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Form')
                ->getAttributeSetsSelectElement($this->getRequest()->getParam('target_country'))
                ->toHtml()
        );
    }
}
