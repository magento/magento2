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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Changes factory model. Creates right Change instance by given type.
 */
class Mage_DesignEditor_Model_Change_Factory
{
    /**
     * Create instance of change by given type
     *
     * @static
     * @param Varien_Object|array $change
     * @throws Magento_Exception
     * @return Mage_DesignEditor_Model_ChangeAbstract
     */
    public static function getInstance($change)
    {
        $class = self::getClass($change);
        $model = Mage::getModel($class, $change);
        if (!$model instanceof Mage_DesignEditor_Model_ChangeAbstract) {
            throw new Magento_Exception(sprintf('Invalid change class "%s"', $class));
        }
        return $model;
    }

    /**
     * Build change class using given type
     *
     * @static
     * @param Varien_Object|array $change
     * @return string
     */
    public static function getClass($change)
    {
        $type = self::_getChangeType($change);
        if ($type == Mage_DesignEditor_Model_Change_LayoutAbstract::CHANGE_TYPE) {
            $directive = self::_getChangeLayoutDirective($change);
            $class = 'Mage_DesignEditor_Model_Change_' . ucfirst($type) . '_' . ucfirst($directive);
        } else {
            $class = 'Mage_DesignEditor_Model_Change_' . ucfirst($type);
        }

        return $class;
    }

    /**
     * Get change type
     *
     * @param Varien_Object|array $change
     * @throws Magento_Exception
     * @return string
     */
    protected static function _getChangeType($change)
    {
        $type = null;
        if (is_array($change)) {
            $type = isset($change['type']) ? $change['type'] : null;
        } elseif ($change instanceof Varien_Object) {
            $type = $change->getType();
        }

        if (!$type) {
            throw new Magento_Exception('Impossible to get change type');
        }

        return $type;
    }

    /**
     * Get change layout directive
     *
     * @param Varien_Object|array $change
     * @throws Magento_Exception
     * @return string
     */
    protected static function _getChangeLayoutDirective($change)
    {
        $directive = null;
        if (is_array($change)) {
            $directive = isset($change['action_name']) ? $change['action_name'] : null;
        } elseif ($change instanceof Varien_Object) {
            $directive = $change->getActionName();
        }

        if (!$directive) {
            throw new Magento_Exception('Impossible to get layout change directive');
        }

        return $directive;
    }
}
