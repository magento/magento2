<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Chooser
 *
 * @since 2.0.0
 */
class Chooser extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Chooser source action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $chooserBlock = $this->_view->getLayout()->createBlock(
            \Magento\SalesRule\Block\Adminhtml\Promo\Widget\Chooser::class,
            '',
            ['data' => ['id' => $uniqId]]
        );
        $this->getResponse()->setBody($chooserBlock->toHtml());
    }
}
