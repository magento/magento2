<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Magento\Backend\App\Action\Context;
use Magento\UrlRewrite\Model\Modes;
use Magento\UrlRewrite\Block\Selector;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

class Edit extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * @var \Magento\UrlRewrite\Model\Modes
     */
    protected $modesInstance;
    /**
     * @var \Magento\UrlRewrite\Block\Selector
     */
    protected $selector;

    /**
     * @param Selector $selector
     * @param Modes $modesInstance
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param Context $context
     */
    public function __construct(
        Selector $selector,
        Modes $modesInstance,
        UrlRewriteFactory $urlRewriteFactory,
        Context $context
    )
    {
        $this->selector = $selector;
        $this->modesInstance = $modesInstance;
        parent::__construct($context, $urlRewriteFactory);
    }
    /**
     * Get current mode
     *
     * @return string
     */
    protected function _getMode()
    {
        $mode = null;
        $mode = $this->modesInstance->getModeByUrlRewrite($this->_getUrlRewrite());
        if (is_null($mode)) {
            $mode = $this->selector->getDefaultMode();
        }
        return $mode;
    }

    /**
     * Show urlrewrite edit/create page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_UrlRewrite::urlrewrite');

        $mode = $this->_getMode();
        $editBlock = $this->modesInstance->getBlockInstance(
            $mode,
            [
                'data' => [
                    'url_rewrite' => $this->_getUrlRewrite()
                ]
            ]
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend($editBlock->getHeaderText());
        $this->_addContent($editBlock);
        $this->_view->renderLayout();
    }
}
