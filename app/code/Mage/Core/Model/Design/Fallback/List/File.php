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
 * Fallback rules list for non-public files
 */
class Mage_Core_Model_Design_Fallback_List_File extends Mage_Core_Model_Design_Fallback_List_ListAbstract
{
    /**
     * Set rules in proper order for specific fallback procedure
     *
     * @return array of rules Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    protected function _getFallbackRules()
    {
        return array(
            new Mage_Core_Model_Design_Fallback_Rule_Theme(array(
                new Mage_Core_Model_Design_Fallback_Rule_Simple(
                    $this->_dir->getDir(Mage_Core_Model_Dir::THEMES) . '/<area>/<theme_path>'
                ),
                new Mage_Core_Model_Design_Fallback_Rule_Simple(
                    $this->_dir->getDir(Mage_Core_Model_Dir::THEMES) . '/<area>/<theme_path>/<namespace>_<module>',
                    array('namespace', 'module')
                ),
            )),
            new Mage_Core_Model_Design_Fallback_Rule_Simple(
                $this->_dir->getDir(Mage_Core_Model_Dir::MODULES) . '/<namespace>/<module>/view/<area>',
                array('namespace', 'module')
            )
        );
    }
}
