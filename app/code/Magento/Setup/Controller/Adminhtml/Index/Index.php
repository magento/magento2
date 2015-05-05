<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller\Adminhtml\Index;

use Magento\Backend\App\AbstractAction;

class Index extends AbstractAction
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['index'];
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->getUrl('setup/index/index', []) . '?app=setup';
        return $resultRedirect->setUrl($url);
    }
}