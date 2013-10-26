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
namespace Magento\Core\Model\Design\Fallback;

class Factory
{
    /**
     * @var \Magento\App\Dir
     */
    private $_dirs;

    /**
     * Constructor
     *
     * @param \Magento\App\Dir $dirs
     */
    public function __construct(\Magento\App\Dir $dirs)
    {
        $this->_dirs = $dirs;
    }

    /**
     * Retrieve newly created fallback rule for locale files, such as CSV translation maps
     *
     * @return \Magento\Core\Model\Design\Fallback\Rule\RuleInterface
     */
    public function createLocaleFileRule()
    {
        $themesDir = $this->_dirs->getDir(\Magento\App\Dir::THEMES);
        return new \Magento\Core\Model\Design\Fallback\Rule\Theme(
            new \Magento\Core\Model\Design\Fallback\Rule\Simple("$themesDir/<area>/<theme_path>/i18n/<locale>")
        );
    }

    /**
     * Retrieve newly created fallback rule for dynamic view files, such as layouts and templates
     *
     * @return \Magento\Core\Model\Design\Fallback\Rule\RuleInterface
     */
    public function createFileRule()
    {
        $themesDir = $this->_dirs->getDir(\Magento\App\Dir::THEMES);
        $modulesDir = $this->_dirs->getDir(\Magento\App\Dir::MODULES);
        return new \Magento\Core\Model\Design\Fallback\Rule\ModularSwitch(
            new \Magento\Core\Model\Design\Fallback\Rule\Theme(
                new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                    "$themesDir/<area>/<theme_path>"
                )
            ),
            new \Magento\Core\Model\Design\Fallback\Rule\Composite(array(
                new \Magento\Core\Model\Design\Fallback\Rule\Theme(
                    new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                        "$themesDir/<area>/<theme_path>/<namespace>_<module>"
                    )
                ),
                new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                    "$modulesDir/<namespace>/<module>/view/<area>"
                ),
            ))
        );
    }

    /**
     * Retrieve newly created fallback rule for static view files, such as CSS, JavaScript, images, etc.
     *
     * @return \Magento\Core\Model\Design\Fallback\Rule\RuleInterface
     */
    public function createViewFileRule()
    {
        $themesDir = $this->_dirs->getDir(\Magento\App\Dir::THEMES);
        $modulesDir = $this->_dirs->getDir(\Magento\App\Dir::MODULES);
        $pubLibDir = $this->_dirs->getDir(\Magento\App\Dir::PUB_LIB);
        return new \Magento\Core\Model\Design\Fallback\Rule\ModularSwitch(
            new \Magento\Core\Model\Design\Fallback\Rule\Composite(array(
                new \Magento\Core\Model\Design\Fallback\Rule\Theme(
                    new \Magento\Core\Model\Design\Fallback\Rule\Composite(array(
                        new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                            "$themesDir/<area>/<theme_path>/i18n/<locale>", array('locale')
                        ),
                        new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                            "$themesDir/<area>/<theme_path>"
                        ),
                    ))
                ),
                new \Magento\Core\Model\Design\Fallback\Rule\Simple($pubLibDir),
            )),
            new \Magento\Core\Model\Design\Fallback\Rule\Composite(array(
                new \Magento\Core\Model\Design\Fallback\Rule\Theme(
                    new \Magento\Core\Model\Design\Fallback\Rule\Composite(array(
                        new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                            "$themesDir/<area>/<theme_path>/i18n/<locale>/<namespace>_<module>", array('locale')
                        ),
                        new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                            "$themesDir/<area>/<theme_path>/<namespace>_<module>"
                        ),
                    ))
                ),
                new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                    "$modulesDir/<namespace>/<module>/view/<area>/i18n/<locale>", array('locale')
                ),
                new \Magento\Core\Model\Design\Fallback\Rule\Simple(
                    "$modulesDir/<namespace>/<module>/view/<area>"
                ),
            ))
        );
    }
}
