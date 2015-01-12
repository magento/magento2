<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\Fallback;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
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
        $themesDir = $this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath();
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
        $themesDir = $this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath();
        $modulesDir = $this->filesystem->getDirectoryRead(DirectoryList::MODULES)->getAbsolutePath();
        return new ModularSwitch(
            new Theme(
                new Simple("$themesDir/<area>/<theme_path>/templates")
            ),
            new Composite(
                [
                    new Theme(new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>/templates")),
                    new Simple("$modulesDir/<namespace>/<module>/view/<area>/templates"),
                    new Simple("$modulesDir/<namespace>/<module>/view/base/templates"),
                ]
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
        $themesDir = $this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath();
        $modulesDir = $this->filesystem->getDirectoryRead(DirectoryList::MODULES)->getAbsolutePath();
        return new ModularSwitch(
            new Theme(new Simple("$themesDir/<area>/<theme_path>")),
            new Composite(
                [
                    new Theme(new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>")),
                    new Simple("$modulesDir/<namespace>/<module>/view/<area>"),
                    new Simple("{$modulesDir}/<namespace>/<module>/view/base"),
                ]
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
        $themesDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath(), '/');
        $modulesDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::MODULES)->getAbsolutePath(), '/');
        $libDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::LIB_WEB)->getAbsolutePath(), '/');
        return new ModularSwitch(
            new Composite(
                [
                    new Theme(
                        new Composite(
                            [
                                new Simple("$themesDir/<area>/<theme_path>/web/i18n/<locale>", ['locale']),
                                new Simple("$themesDir/<area>/<theme_path>/web"),
                            ]
                        )
                    ),
                    new Simple($libDir),
                ]
            ),
            new Composite(
                [
                    new Theme(
                        new Composite(
                            [
                                new Simple(
                                    "$themesDir/<area>/<theme_path>/<namespace>_<module>/web/i18n/<locale>",
                                    ['locale']
                                ),
                                new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>/web"),
                            ]
                        )
                    ),
                    new Simple(
                        "$modulesDir/<namespace>/<module>/view/<area>/web/i18n/<locale>",
                        ['locale']
                    ),
                    new Simple(
                        "$modulesDir/<namespace>/<module>/view/base/web/i18n/<locale>",
                        ['locale']
                    ),
                    new Simple("$modulesDir/<namespace>/<module>/view/<area>/web"),
                    new Simple("{$modulesDir}/<namespace>/<module>/view/base/web"),
                ]
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
