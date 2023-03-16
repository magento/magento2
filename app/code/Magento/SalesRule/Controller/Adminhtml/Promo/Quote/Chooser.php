<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\SalesRule\Block\Adminhtml\Promo\Widget\Chooser as PromoWidgetChooser;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote as AdminhtmlPromoQuote;

class Chooser extends AdminhtmlPromoQuote
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
            PromoWidgetChooser::class,
            '',
            ['data' => ['id' => $uniqId]]
        );
        $this->getResponse()->setBody($chooserBlock->toHtml());
    }
}
