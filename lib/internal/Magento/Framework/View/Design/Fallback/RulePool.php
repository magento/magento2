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

namespace Magento\Framework\View\Design\Fallback;

use Magento\Framework\App\Filesystem;
use Magento\Framework\View\Design\Fallback\Rule\Composite;
use Magento\Framework\View\Design\Fallback\Rule\ModularSwitch;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\Rule\Simple;
use Magento\Framework\View\Design\Fallback\Rule\Theme;

/**
 * Fallback Factory
 *
 * Factory that produces all sorts of fallback rules
 */
class RulePool
{
    /**#@+
     * Supported types of fallback rules
     */
    const TYPE_FILE = 'file';
    const TYPE_LOCALE_FILE = 'locale';
    const TYPE_TEMPLATE_FILE = 'template';
    const TYPE_STATIC_FILE = 'static';
    /**#@-*/

    /**
     * File system
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    private $rules = [];

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
    protected function createLocaleFileRule()
    {
        $themesDir = $this->filesystem->getPath(Filesystem::THEMES_DIR);
        return new Theme(
            new Simple("$themesDir/<area>/<theme_path>")
        );
    }

    /**
     * Retrieve newly created fallback rule for template files
     *
     * @return RuleInterface
     */
    protected function createTemplateFileRule()
    {
        $themesDir = $this->filesystem->getPath(Filesystem::THEMES_DIR);
        $modulesDir = $this->filesystem->getPath(Filesystem::MODULES_DIR);
        return new ModularSwitch(
            new Theme(
                new Simple("$themesDir/<area>/<theme_path>/templates")
            ),
            new Composite(
                array(
                    new Theme(new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>/templates")),
                    new Simple("$modulesDir/<namespace>/<module>/view/<area>/templates"),
                    new Simple("$modulesDir/<namespace>/<module>/view/base/templates"),
                )
            )
        );
    }

    /**
     * Retrieve newly created fallback rule for dynamic view files
     *
     * @return RuleInterface
     */
    protected function createFileRule()
    {
        $themesDir = $this->filesystem->getPath(Filesystem::THEMES_DIR);
        $modulesDir = $this->filesystem->getPath(Filesystem::MODULES_DIR);
        return new ModularSwitch(
            new Theme(new Simple("$themesDir/<area>/<theme_path>")),
            new Composite(
                array(
                    new Theme(new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>")),
                    new Simple("$modulesDir/<namespace>/<module>/view/<area>"),
                    new Simple("{$modulesDir}/<namespace>/<module>/view/base"),
                )
            )
        );
    }

    /**
     * Retrieve newly created fallback rule for static view files, such as CSS, JavaScript, images, etc.
     *
     * @return RuleInterface
     */
    protected function createViewFileRule()
    {
        $themesDir = $this->filesystem->getPath(Filesystem::THEMES_DIR);
        $modulesDir = $this->filesystem->getPath(Filesystem::MODULES_DIR);
        $libDir = $this->filesystem->getPath(Filesystem::LIB_WEB);
        return new ModularSwitch(
            new Composite(
                array(
                    new Theme(
                        new Composite(
                            array(
                                new Simple("$themesDir/<area>/<theme_path>/web/i18n/<locale>", array('locale')),
                                new Simple("$themesDir/<area>/<theme_path>/web"),
                            )
                        )
                    ),
                    new Simple($libDir),
                )
            ),
            new Composite(
                array(
                    new Theme(
                        new Composite(
                            array(
                                new Simple(
                                    "$themesDir/<area>/<theme_path>/<namespace>_<module>/web/i18n/<locale>",
                                    array('locale')
                                ),
                                new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>/web"),
                            )
                        )
                    ),
                    new Simple(
                        "$modulesDir/<namespace>/<module>/view/<area>/web/i18n/<locale>",
                        array('locale')
                    ),
                    new Simple(
                        "$modulesDir/<namespace>/<module>/view/base/web/i18n/<locale>",
                        array('locale')
                    ),
                    new Simple("$modulesDir/<namespace>/<module>/view/<area>/web"),
                    new Simple("{$modulesDir}/<namespace>/<module>/view/base/web"),
                )
            )
        );
    }

    /**
     * @param string $type
     * @return RuleInterface
     * @throws \InvalidArgumentException
     */
    public function getRule($type)
    {
        if (isset($this->rules[$type])) {
            return $this->rules[$type];
        }
        switch ($type) {
            case self::TYPE_FILE:
                $rule = $this->createFileRule();
                break;
            case self::TYPE_LOCALE_FILE:
                $rule = $this->createLocaleFileRule();
                break;
            case self::TYPE_TEMPLATE_FILE:
                $rule = $this->createTemplateFileRule();
                break;
            case self::TYPE_STATIC_FILE:
                $rule = $this->createViewFileRule();
                break;
            default:
                throw new \InvalidArgumentException("Fallback rule '$type' is not supported");
        }
        $this->rules[$type] = $rule;
        return $this->rules[$type];
    }
}
