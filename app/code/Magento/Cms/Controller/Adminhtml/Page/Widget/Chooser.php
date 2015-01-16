<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Page\Widget;

class Chooser extends \Magento\Backend\App\Action
{
    /**
     * Chooser Source action
     *
     * @return void
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $pagesGrid = $this->_view->getLayout()->createBlock(
            'Magento\Cms\Block\Adminhtml\Page\Widget\Chooser',
            '',
            ['data' => ['id' => $uniqId]]
        );
        $this->getResponse()->setBody($pagesGrid->toHtml());
    }
}
