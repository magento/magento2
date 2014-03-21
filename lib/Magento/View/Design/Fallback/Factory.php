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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\View\Design\Fallback;

use Magento\App\Filesystem;
use Magento\View\Design\Fallback\Rule\Composite;
use Magento\View\Design\Fallback\Rule\ModularSwitch;
use Magento\View\Design\Fallback\Rule\RuleInterface;
use Magento\View\Design\Fallback\Rule\Simple;
use Magento\View\Design\Fallback\Rule\Theme;

/**
 * Fallback Factory
 *
 * Factory that produces all sorts of fallback rules
 */
class Factory
{
    /**
     * File system
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Retrieve newly created fallback rule for locale files, such as CSV translation maps
     *
     * @return RuleInterface
     */
    public function createLocaleFileRule()
    {
        $themesDir = $this->filesystem->getPath(Filesystem::THEMES_DIR);
        return new Theme(new Simple("{$themesDir}/<area>/<theme_path>/i18n/<locale>"));
    }

    /**
     * Retrieve newly created fallback rule for dynamic view files, such as layouts and templates
     *
     * @return RuleInterface
     */
    public function createFileRule()
    {
        $themesDir = $this->filesystem->getPath(Filesystem::THEMES_DIR);
        $modulesDir = $this->filesystem->getPath(Filesystem::MODULES_DIR);
        return new ModularSwitch(
            new Theme(new Simple("{$themesDir}/<area>/<theme_path>")),
            new Composite(
                array(
                    new Theme(new Simple("{$themesDir}/<area>/<theme_path>/<namespace>_<module>")),
                    new Simple("{$modulesDir}/<namespace>/<module>/view/<area>")
                )
            )
        );
    }

    /**
     * Retrieve newly created fallback rule for static view files, such as CSS, JavaScript, images, etc.
     *
     * @return RuleInterface
     */
    public function createViewFileRule()
    {
        $themesDir = $this->filesystem->getPath(Filesystem::THEMES_DIR);
        $modulesDir = $this->filesystem->getPath(Filesystem::MODULES_DIR);
        $pubLibDir = $this->filesystem->getPath(Filesystem::PUB_LIB_DIR);
        return new ModularSwitch(
            new Composite(
                array(
                    new Theme(
                        new Composite(
                            array(
                                new Simple("{$themesDir}/<area>/<theme_path>/i18n/<locale>", array('locale')),
                                new Simple("{$themesDir}/<area>/<theme_path>")
                            )
                        )
                    ),
                    new Simple($pubLibDir)
                )
            ),
            new Composite(
                array(
                    new Theme(
                        new Composite(
                            array(
                                new Simple(
                                    "{$themesDir}/<area>/<theme_path>/i18n/<locale>/<namespace>_<module>",
                                    array('locale')
                                ),
                                new Simple("{$themesDir}/<area>/<theme_path>/<namespace>_<module>")
                            )
                        )
                    ),
                    new Simple("{$modulesDir}/<namespace>/<module>/view/<area>/i18n/<locale>", array('locale')),
                    new Simple("{$modulesDir}/<namespace>/<module>/view/<area>")
                )
            )
        );
    }
}
