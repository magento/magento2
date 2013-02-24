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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Abstract configuration class
 *
 * Used to retrieve core configuration values
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Core_Model_Config_Base extends Varien_Simplexml_Config implements Mage_Core_Model_ConfigInterface
{
    /**
     * List of instances
     *
     * @var array
     */
    public static $instances = array();

    /**
     * @param string|Varien_Simplexml_Element $sourceData $sourceData
     */
    public function __construct($sourceData = null)
    {
        $this->_elementClass = 'Mage_Core_Model_Config_Element';
        parent::__construct($sourceData);
        self::$instances[] = $this;
    }

    /**
     * Reinitialize config object
     */
    public function reinit()
    {

    }

    /**
     * Cleanup objects because of simplexml memory leak
     */
    public static function destroy()
    {
        foreach (self::$instances  as $instance) {
            $instance->_xml = null;
        }
        self::$instances = array();
    }
}
