<?php
/**
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

/**
 * Layout config processor
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\View\Layout\Argument;

class HandlerFactory
{
    /**
     * Array of argument handler factories
     * @var array
     */
    protected $_handlerFactories = array();

    /**
     * Argument handlers list
     *
     * @var array
     */
    protected $_argumentHandlers = array();

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param array $handlerFactories
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        array $handlerFactories = array()
    ) {
        $this->_objectManager = $objectManager;
        $this->_handlerFactories = $handlerFactories;
    }

    /**
     * Get argument handler factory by given type
     * @param string $type
     * @return \Magento\View\Layout\Argument\HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function getArgumentHandlerByType($type)
    {
        if (false == is_string($type)) {
            throw new \InvalidArgumentException('Passed invalid argument handler type');
        }

        if (!isset($this->_handlerFactories[$type])) {
            throw new \InvalidArgumentException("Argument handler {$type} does not exist");
        }

        if (isset($this->_argumentHandlers[$type])) {
            return $this->_argumentHandlers[$type];
        }
        /** @var $handler \Magento\View\Layout\Argument\HandlerInterface */
        $handler = $this->_objectManager->create($this->_handlerFactories[$type], array());

        if (false === ($handler instanceof \Magento\View\Layout\Argument\HandlerInterface)) {
            throw new \InvalidArgumentException(
                "{$type} type handler must implement \\Magento\\View\\Layout\\Argument\\HandlerInterface"
            );
        }

        $this->_argumentHandlers[$type] = $handler;
        return $handler;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return array_keys($this->_handlerFactories);
    }
}
