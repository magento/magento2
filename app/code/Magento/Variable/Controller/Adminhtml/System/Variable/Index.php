<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Display Variables list page
 * @api
 * @since 100.0.2
 */
class Index extends \Magento\Variable\Controller\Adminhtml\System\Variable implements HttpGetActionInterface
{
    /**
     * Index Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Variables'));
        return $resultPage;
    }
}
