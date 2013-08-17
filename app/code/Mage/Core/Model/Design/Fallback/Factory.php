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
 * Factory that produces all sorts of fallback rules
 */
class Mage_Core_Model_Design_Fallback_Factory
{
    /**
     * @var Mage_Core_Model_Dir
     */
    private $_dirs;

    /**
     * Constructor
     *
     * @param Mage_Core_Model_Dir $dirs
     */
    public function __construct(Mage_Core_Model_Dir $dirs)
    {
        $this->_dirs = $dirs;
    }

    /**
     * Retrieve newly created fallback rule for locale files, such as CSV translation maps
     *
     * @return Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    public function createLocaleFileRule()
    {
        $themesDir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        return new Mage_Core_Model_Design_Fallback_Rule_Theme(
            new Mage_Core_Model_Design_Fallback_Rule_Simple("$themesDir/<area>/<theme_path>/locale/<locale>")
        );
    }

    /**
     * Retrieve newly created fallback rule for dynamic view files, such as layouts and templates
     *
     * @return Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    public function createFileRule()
    {
        $themesDir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        $modulesDir = $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES);
        return new Mage_Core_Model_Design_Fallback_Rule_ModularSwitch(
            new Mage_Core_Model_Design_Fallback_Rule_Theme(
                new Mage_Core_Model_Design_Fallback_Rule_Simple(
                    "$themesDir/<area>/<theme_path>"
                )
            ),
            new Mage_Core_Model_Design_Fallback_Rule_Composite(array(
                new Mage_Core_Model_Design_Fallback_Rule_Theme(
                    new Mage_Core_Model_Design_Fallback_Rule_Simple(
                        "$themesDir/<area>/<theme_path>/<namespace>_<module>"
                    )
                ),
                new Mage_Core_Model_Design_Fallback_Rule_Simple(
                    "$modulesDir/<namespace>/<module>/view/<area>"
                ),
            ))
        );
    }

    /**
     * Retrieve newly created fallback rule for static view files, such as CSS, JavaScript, images, etc.
     *
     * @return Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    public function createViewFileRule()
    {
        $themesDir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        $modulesDir = $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES);
        $pubLibDir = $this->_dirs->getDir(Mage_Core_Model_Dir::PUB_LIB);
        return new Mage_Core_Model_Design_Fallback_Rule_ModularSwitch(
            new Mage_Core_Model_Design_Fallback_Rule_Composite(array(
                new Mage_Core_Model_Design_Fallback_Rule_Theme(
                    new Mage_Core_Model_Design_Fallback_Rule_Composite(array(
                        new Mage_Core_Model_Design_Fallback_Rule_Simple(
                            "$themesDir/<area>/<theme_path>/locale/<locale>", array('locale')
                        ),
                        new Mage_Core_Model_Design_Fallback_Rule_Simple(
                            "$themesDir/<area>/<theme_path>"
                        ),
                    ))
                ),
                new Mage_Core_Model_Design_Fallback_Rule_Simple($pubLibDir),
            )),
            new Mage_Core_Model_Design_Fallback_Rule_Composite(array(
                new Mage_Core_Model_Design_Fallback_Rule_Theme(
                    new Mage_Core_Model_Design_Fallback_Rule_Composite(array(
                        new Mage_Core_Model_Design_Fallback_Rule_Simple(
                            "$themesDir/<area>/<theme_path>/locale/<locale>/<namespace>_<module>", array('locale')
                        ),
                        new Mage_Core_Model_Design_Fallback_Rule_Simple(
                            "$themesDir/<area>/<theme_path>/<namespace>_<module>"
                        ),
                    ))
                ),
                new Mage_Core_Model_Design_Fallback_Rule_Simple(
                    "$modulesDir/<namespace>/<module>/view/<area>/locale/<locale>", array('locale')
                ),
                new Mage_Core_Model_Design_Fallback_Rule_Simple(
                    "$modulesDir/<namespace>/<module>/view/<area>"
                ),
            ))
        );
    }
}
