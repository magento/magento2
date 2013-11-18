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
 * Layout object abstract argument
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument;

abstract class AbstractHandler
    implements \Magento\Core\Model\Layout\Argument\HandlerInterface
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Retrieve value from argument
     *
     * @param \Magento\View\Layout\Element $argument
     * @return mixed|null
     */
    protected function _getArgumentValue(\Magento\View\Layout\Element $argument)
    {
        if ($this->_isUpdater($argument)) {
            return null;
        }
        if (isset($argument->value)) {
            $value = $argument->value;
        } else {
            $value = $argument;
        }
        return trim((string)$value);
    }

    /**
     * Check whether updater used and value not overwriten
     *
     * @param \Magento\View\Layout\Element $argument
     * @return string
     */
    protected function _isUpdater(\Magento\View\Layout\Element $argument)
    {
        $updaters = $argument->xpath('./updater');
        if (!empty($updaters) && !isset($argument->value)) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve xsi:type attribute value from argument
     *
     * @param \Magento\View\Layout\Element $argument
     * @return string
     */
    protected function _getArgumentType(\Magento\View\Layout\Element $argument)
    {
        return (string)$argument->attributes('xsi', true)->type;
    }

    /**
     * Parse argument node
     * @param \Magento\View\Layout\Element $argument
     * @return array
     */
    public function parse(\Magento\View\Layout\Element $argument)
    {
        $result = array();
        $updaters = array();
        $result['type'] = $this->_getArgumentType($argument);
        foreach ($argument->xpath('./updater') as $updaterNode) {
            /** @var $updaterNode \Magento\View\Layout\Element */
            $updaters[uniqid() . '_' . mt_rand()] = trim((string)$updaterNode);
        }

        $result = !empty($updaters) ? $result + array('updaters' => $updaters) : $result;
        $argumentValue = $this->_getArgumentValue($argument);
        if (isset($argumentValue)) {
            $result = array_merge_recursive($result, array(
                'value' => $argumentValue
            ));
        }
        return $result;
    }

    /**
     * Validate parsed argument before processing
     *
     * @param array $argument
     * @throws \InvalidArgumentException
     */
    protected function _validate(array $argument)
    {
        if (!isset($argument['value'])) {
            throw new \InvalidArgumentException(
                'Value is required for argument. ' . $this->_getArgumentInfo($argument)
            );
        }
    }

    /**
     * @param array $argument
     * @return string
     */
    protected function _getArgumentInfo($argument)
    {
        return  'Argument: ' . json_encode($argument);
    }
}
