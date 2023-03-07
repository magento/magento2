<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Variable\Controller\Adminhtml\System\Variable;

/**
 * Display Variables list page
 * @api
 * @since 100.0.2
 */
class Index extends Variable implements HttpGetActionInterface
{
    /**
     * Index Action
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Variables'));
        return $resultPage;
    }
}
