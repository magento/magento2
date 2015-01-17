<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

class Chooser extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Chooser source action
     *
     * @return void
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $chooserBlock = $this->_view->getLayout()->createBlock(
            'Magento\SalesRule\Block\Adminhtml\Promo\Widget\Chooser',
            '',
            ['data' => ['id' => $uniqId]]
        );
        $this->getResponse()->setBody($chooserBlock->toHtml());
    }
}
