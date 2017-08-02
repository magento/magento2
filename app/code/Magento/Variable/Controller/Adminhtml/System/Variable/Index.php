<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

/**
 * Display Variables list page
 * @api
 * @since 2.0.0
 */
class Index extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * Index Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Variables'));
        return $resultPage;
    }
}
