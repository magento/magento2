<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller\Block;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Page cache render controller
 *
 * @deprecated 100.3.4
 */
class Render extends \Magento\PageCache\Controller\Block implements HttpGetActionInterface
{
    /**
     * Returns block content depends on ajax request
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }
        // disable profiling during private content handling AJAX call
        \Magento\Framework\Profiler::reset();
        $currentRoute = $this->getRequest()->getRouteName();
        $currentControllerName = $this->getRequest()->getControllerName();
        $currentActionName = $this->getRequest()->getActionName();
        $currentRequestUri = $this->getRequest()->getRequestUri();

        $origRequest = $this->getRequest()->getParam('originalRequest');
        if ($origRequest && is_string($origRequest)) {
            $origRequest = json_decode($origRequest, true);
        }
        $this->getRequest()->setRouteName($origRequest['route']);
        $this->getRequest()->setControllerName($origRequest['controller']);
        $this->getRequest()->setActionName($origRequest['action']);
        $this->getRequest()->setRequestUri($origRequest['uri']);

        /** @var \Magento\Framework\View\Element\BlockInterface[] $blocks */
        $blocks = $this->_getBlocks();
        $data = [];

        foreach ($blocks as $blockName => $blockInstance) {
            $data[$blockName] = $blockInstance->toHtml();
        }

        $this->getRequest()->setRouteName($currentRoute);
        $this->getRequest()->setControllerName($currentControllerName);
        $this->getRequest()->setActionName($currentActionName);
        $this->getRequest()->setRequestUri($currentRequestUri);

        $this->getResponse()->setPrivateHeaders(\Magento\PageCache\Helper\Data::PRIVATE_MAX_AGE_CACHE);
        $this->translateInline->processResponseBody($data);
        $this->getResponse()->appendBody(json_encode($data));
    }
}
