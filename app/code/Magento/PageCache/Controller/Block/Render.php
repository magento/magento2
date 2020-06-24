<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller\Block;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Render extends \Magento\PageCache\Controller\Block implements HttpGetActionInterface
{
    /**
     * Returns block content depends on ajax request
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->validateRequestParameters()) {
            $this->_forward('noroute');
            return;
        }
        try {
            // disable profiling during private content handling AJAX call
            \Magento\Framework\Profiler::reset();
            $currentRoute = $this->getRequest()->getRouteName();
            $currentControllerName = $this->getRequest()->getControllerName();
            $currentActionName = $this->getRequest()->getActionName();
            $currentRequestUri = $this->getRequest()->getRequestUri();
            if (!$this->validateAndProcessOriginalRequest()) {
                $this->_forward('noroute');
                return;
            }
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
        } catch (\Exception $e) {
            //Log error and forward to no-route.
            $this->logger->critical($e);
            $this->_forward('noroute');
            return;
        }
    }

    /**
     * Validate request parameters.
     *
     * @return bool
     */
    private function validateRequestParameters()
    {
        if (!$this->getRequest()->isAjax()
            || !$this->getRequest()->getRouteName()
            || !$this->getRequest()->getControllerName()
            || !$this->getRequest()->getActionName()
            || !$this->getRequest()->getRequestUri()) {
            return false;
        }
        return true;
    }

    /**
     * Validate and process original request parameter.
     *
     * @return bool
     */
    private function validateAndProcessOriginalRequest()
    {
        $origRequest = $this->getRequest()->getParam('originalRequest');
        if ($origRequest !== null && $origRequest && is_string($origRequest)) {
            $origRequest = $this->unserialize($origRequest);
            if (!is_array($origRequest)
                || !isset($origRequest['route'])
                || !isset($origRequest['controller'])
                || !isset($origRequest['action'])
                || !isset($origRequest['uri'])) {
                return false;
            }
            $this->getRequest()->setRouteName($origRequest['route']);
            $this->getRequest()->setControllerName($origRequest['controller']);
            $this->getRequest()->setActionName($origRequest['action']);
            $this->getRequest()->setRequestUri($origRequest['uri']);
        }
        return true;
    }
}
