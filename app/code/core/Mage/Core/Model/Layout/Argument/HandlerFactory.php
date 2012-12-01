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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout config processor
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Layout_Argument_HandlerFactory
{
    const LAYOUT_ARGUMENT_TYPE_OBJECT  = 'object';
    const LAYOUT_ARGUMENT_TYPE_OPTIONS = 'options';
    const LAYOUT_ARGUMENT_TYPE_URL     = 'url';

    /**
     * Array of argument handler factories
     * @var array
     */
    protected $_handlerFactories = array(
        self::LAYOUT_ARGUMENT_TYPE_OBJECT  => 'Mage_Core_Model_Layout_Argument_Handler_Object',
        self::LAYOUT_ARGUMENT_TYPE_OPTIONS => 'Mage_Core_Model_Layout_Argument_Handler_Options',
        self::LAYOUT_ARGUMENT_TYPE_URL     => 'Mage_Core_Model_Layout_Argument_Handler_Url'
    );

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get argument handler factory by given type
     * @param string $type
     * @return Mage_Core_Model_Layout_Argument_HandlerInterface
     * @throws InvalidArgumentException
     */
    public function getArgumentHandlerByType($type)
    {
        if (false == is_string($type)) {
            throw new InvalidArgumentException('Passed invalid argument handler type');
        }

        if (!isset($this->_handlerFactories[$type])) {
            throw new InvalidArgumentException('Argument handler ' . $type . ' is not exists');
        }

        return $this->_objectManager->create($this->_handlerFactories[$type], array(), false);
    }
}
