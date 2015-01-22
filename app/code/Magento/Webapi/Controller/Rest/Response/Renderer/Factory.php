<?php
/**
 * Factory of REST renders
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest\Response\Renderer;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /** @var \Magento\Webapi\Controller\Rest\Request */
    protected $_request;

    /**
     * @var array
     */
    protected $_renders;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Webapi\Controller\Rest\Request $request
     * @param array $renders
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Webapi\Controller\Rest\Request $request,
        array $renders = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_request = $request;
        $this->_renders = $renders;
    }

    /**
     * Get renderer for Mime-Type specified in Accept header of request.
     *
     * @return \Magento\Webapi\Controller\Rest\Response\RendererInterface
     * @throws \Magento\Webapi\Exception
     * @throws \LogicException
     */
    public function get()
    {
        $renderer = $this->_objectManager->get($this->_getRendererClass());
        if (!$renderer instanceof \Magento\Webapi\Controller\Rest\Response\RendererInterface) {
            throw new \LogicException(
                'The renderer must implement "Magento\Webapi\Controller\Rest\Response\RendererInterface".'
            );
        }
        return $renderer;
    }

    /**
     * Find renderer which can render response in requested format.
     *
     * @return string
     * @throws \Magento\Webapi\Exception
     */
    protected function _getRendererClass()
    {
        $acceptTypes = $this->_request->getAcceptTypes();
        if (!is_array($acceptTypes)) {
            $acceptTypes = [$acceptTypes];
        }
        foreach ($acceptTypes as $acceptType) {
            foreach ($this->_renders as $rendererConfig) {
                $rendererType = $rendererConfig['type'];
                if ($acceptType == $rendererType || $acceptType == current(
                    explode('/', $rendererType)
                ) . '/*' || $acceptType == '*/*'
                ) {
                    return $rendererConfig['model'];
                }
            }
        }
        /** If server does not have renderer for any of the accepted types it SHOULD send 406 (not acceptable). */
        throw new \Magento\Webapi\Exception(
            __(
                'Server cannot match any of the given Accept HTTP header media type(s) from the request: "%1" '.
                'with media types from the config of response renderer.',
                implode(',', $acceptTypes)
            ),
            0,
            \Magento\Webapi\Exception::HTTP_NOT_ACCEPTABLE
        );
    }
}
