<?php
/**
 * Composite http request handler. Used to apply multiple request handlers
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Http_Handler_Composite implements Magento_Http_HandlerInterface
{
    /**
     * Leaf request handlers
     *
     * @var Magento_Http_HandlerInterface[]
     */
    protected $_children;

    /**
     * Handler factory
     *
     * @var Magento_Http_HandlerFactory
     */
    protected $_handlerFactory;

    /**
     * @param Magento_Http_HandlerFactory $factory
     * @param array $handlers
     */
    public function __construct(Magento_Http_HandlerFactory $factory, array $handlers)
    {
        usort($handlers, array($this, '_cmp'));
        $this->_children = $handlers;
        $this->_handlerFactory = $factory;
    }

    /**
     * Sort handlers
     *
     * @param $handlerA
     * @param $handlerB
     * @return int
     */
    protected function _cmp($handlerA, $handlerB)
    {
        $sortOrderA = intval($handlerA['sortOrder']);
        $sortOrderB = intval($handlerB['sortOrder']);
        if ($sortOrderA == $sortOrderB) {
            return 0;
        }
        return ($sortOrderA < $sortOrderB) ? -1 : 1;
    }

    /**
     * Handle http request
     *
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     */
    public function handle(Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        foreach ($this->_children as $handlerConfig) {
            $this->_handlerFactory->create($handlerConfig['class'])->handle($request, $response);
            if ($request->isDispatched()) {
                break;
            }
        }
    }
}

