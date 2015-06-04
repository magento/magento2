<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url;

use Magento\Backend\App\Action;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Backend\App\Action\Context;

/**
 * URL rewrite adminhtml controller
 */
class Rewrite extends Action
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    public function __construct(
        Context $context,
        UrlRewriteFactory $urlRewriteFactory
    )
    {
        $this->urlRewriteFactory = $urlRewriteFactory;
        parent::__construct($context);
    }

    /**
     * Check whether this controller is allowed in admin permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_UrlRewrite::urlrewrite');
    }

    /**
     * Get URL rewrite from request
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected function _getUrlRewrite()
    {
        if (!$this->urlRewrite) {
            $this->urlRewrite = $this->urlRewriteFactory->create();
            $urlRewriteId = (int)$this->getRequest()->getParam('id', 0);
            if ($urlRewriteId) {
                $this->urlRewrite->load($urlRewriteId);
            }
        }
        return $this->urlRewrite;
    }
}
