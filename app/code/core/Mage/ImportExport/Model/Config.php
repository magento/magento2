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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * ImportExport config model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Config
{
    /**
     * Get data about models from specified config key.
     *
     * @static
     * @param string $configKey
     * @throws Mage_Core_Exception
     * @return array
     */
    public static function getModels($configKey)
    {
        $entities = array();

        foreach (Mage::getConfig()->getNode($configKey)->asCanonicalArray() as $entityType => $entityParams) {
            if (empty($entityParams['model_token'])) {
                Mage::throwException(
                    Mage::helper('Mage_ImportExport_Helper_Data')->__('Node does not has model token tag')
                );
            }
            $entities[$entityType] = array(
                'model' => $entityParams['model_token'],
                'label' => empty($entityParams['label']) ? $entityType : $entityParams['label']
            );
        }
        return $entities;
    }

    /**
     * Get model params as combo-box options.
     *
     * @static
     * @param string $configKey
     * @param boolean $withEmpty OPTIONAL Include 'Please Select' option or not
     * @return array
     */
    public static function getModelsComboOptions($configKey, $withEmpty = false)
    {
        $options = array();

        if ($withEmpty) {
            $options[] = array(
                'label' => Mage::helper('Mage_ImportExport_Helper_Data')->__('-- Please Select --'),
                'value' => ''
            );
        }
        foreach (self::getModels($configKey) as $type => $params) {
            $options[] = array('value' => $type, 'label' => $params['label']);
        }
        return $options;
    }

    /**
     * Get model params as array of options.
     *
     * @static
     * @param string $configKey
     * @param boolean $withEmpty OPTIONAL Include 'Please Select' option or not
     * @return array
     */
    public static function getModelsArrayOptions($configKey, $withEmpty = false)
    {
        $options = array();
        if ($withEmpty) {
            $options[0] = Mage::helper('Mage_ImportExport_Helper_Data')->__('-- Please Select --');
        }
        foreach (self::getModels($configKey) as $type => $params) {
            $options[$type] = $params['label'];
        }
        return $options;
    }
}
