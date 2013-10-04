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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout config processor
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument;

class HandlerFactory
{
    const LAYOUT_ARGUMENT_TYPE_OBJECT = 'object';
    const LAYOUT_ARGUMENT_TYPE_OPTIONS = 'options';
    const LAYOUT_ARGUMENT_TYPE_URL = 'url';
    const LAYOUT_ARGUMENT_TYPE_ARRAY = 'array';
    const LAYOUT_ARGUMENT_TYPE_BOOLEAN = 'boolean';
    const LAYOUT_ARGUMENT_TYPE_HELPER = 'helper';
    const LAYOUT_ARGUMENT_TYPE_NUMBER = 'number';
    const LAYOUT_ARGUMENT_TYPE_STRING = 'string';

    /**
     * Array of argument handler factories
     * @var array
     */
    protected $_handlerFactories = array(
        self::LAYOUT_ARGUMENT_TYPE_OBJECT => 'Magento\Core\Model\Layout\Argument\Handler\Object',
        self::LAYOUT_ARGUMENT_TYPE_OPTIONS => 'Magento\Core\Model\Layout\Argument\Handler\Options',
        self::LAYOUT_ARGUMENT_TYPE_URL => 'Magento\Core\Model\Layout\Argument\Handler\Url',
        self::LAYOUT_ARGUMENT_TYPE_ARRAY => 'Magento\Core\Model\Layout\Argument\Handler\ArrayHandler',
        self::LAYOUT_ARGUMENT_TYPE_BOOLEAN => 'Magento\Core\Model\Layout\Argument\Handler\Boolean',
        self::LAYOUT_ARGUMENT_TYPE_HELPER => 'Magento\Core\Model\Layout\Argument\Handler\Helper',
        self::LAYOUT_ARGUMENT_TYPE_NUMBER => 'Magento\Core\Model\Layout\Argument\Handler\Number',
        self::LAYOUT_ARGUMENT_TYPE_STRING => 'Magento\Core\Model\Layout\Argument\Handler\String',
    );

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
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get argument handler factory by given type
     * @param string $type
     * @return \Magento\Core\Model\Layout\Argument\HandlerInterface
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
        /** @var $handler \Magento\Core\Model\Layout\Argument\HandlerInterface */
        $handler = $this->_objectManager->create($this->_handlerFactories[$type], array());

        if (false === ($handler instanceof \Magento\Core\Model\Layout\Argument\HandlerInterface)) {
            throw new \InvalidArgumentException(
                "{$type} type handler must implement \\Magento\\Core\\Model\\Layout\\Argument\\HandlerInterface"
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
