<?php
/**
 * Factory of REST renders
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller\Rest\Response\Renderer;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /** @var \Magento\Webapi\Controller\Rest\Request */
    protected $_request;

    /**
     * @var array
     */
    protected $_renders;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Webapi\Controller\Rest\Request $request
     * @param array $renders
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Webapi\Controller\Rest\Request $request,
        array $renders = array()
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
            $acceptTypes = array($acceptTypes);
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
            __('Server cannot understand Accept HTTP header media type.'),
            0,
            \Magento\Webapi\Exception::HTTP_NOT_ACCEPTABLE
        );
    }
}
