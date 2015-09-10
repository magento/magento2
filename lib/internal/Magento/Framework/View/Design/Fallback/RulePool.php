<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\Fallback;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\Fallback\Rule\Composite;
use Magento\Framework\View\Design\Fallback\Rule\ModularSwitch;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
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
    const TYPE_EMAIL_TEMPLATE = 'email';
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
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\View\Design\Fallback\Rule\SimpleFactory
     */
    private $simpleFactory;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param ComponentRegistrar $componentRegistrar
     * @param \Magento\Framework\View\Design\Fallback\Rule\SimpleFactory $simpleFactory
     */
    public function __construct(
        Filesystem $filesystem,
        ComponentRegistrar $componentRegistrar,
        \Magento\Framework\View\Design\Fallback\Rule\SimpleFactory $simpleFactory
    ) {
        $this->filesystem = $filesystem;
        $this->componentRegistrar = $componentRegistrar;
        $this->simpleFactory = $simpleFactory;
    }

    /**
     * Retrieve newly created fallback rule for locale files, such as CSV translation maps
     *
     * @return RuleInterface
     */
    protected function createLocaleFileRule()
    {
        return new Theme(
            $this->simpleFactory->create(['pattern' => "<theme_dir>/<area>/<theme_path>"])
        );
    }

    /**
     * Retrieve newly created fallback rule for template files
     *
     * @return RuleInterface
     */
    protected function createTemplateFileRule()
    {
        return new ModularSwitch(
            new Theme($this->simpleFactory->create(['pattern' => "<theme_dir>/<area>/<theme_path>/templates"])),
            new Composite(
                [
                    new Theme(
                        $this->simpleFactory->create(
                            ['pattern' => "<theme_dir>/<area>/<theme_path>/<namespace>_<module>/templates"]
                        )
                    ),
                    $this->simpleFactory->create(
                        ['pattern' => "<module_dir>/<namespace>/<module>/view/<area>/templates"]
                    ),
                    $this->simpleFactory->create(
                        ['pattern' => "<module_dir>/<namespace>/<module>/view/base/templates"]
                    ),
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
        return new ModularSwitch(
            new Theme($this->simpleFactory
                ->create(['pattern' => "<theme_dir>/<area>/<theme_path>"])),
            new Composite(
                [
                    new Theme(
                        $this->simpleFactory->create(
                            ['pattern' => "<theme_dir>/<area>/<theme_path>/<namespace>_<module>"]
                        )
                    ),
                    $this->simpleFactory->create(['pattern' => "<module_dir>/<namespace>/<module>/view/<area>"]),
                    $this->simpleFactory->create(['pattern' => "<module_dir>/<namespace>/<module>/view/base"]),
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
        $libDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::LIB_WEB)->getAbsolutePath(), '/');
        return new ModularSwitch(
            new Composite(
                [
                    new Theme(
                        new Composite(
                            [
                                $this->simpleFactory
                                    ->create([
                                        'pattern' => "<theme_dir>/<area>/<theme_path>/web/i18n/<locale>",
                                        'optionalParams' => ['locale']
                                    ]),
                                $this->simpleFactory
                                    ->create(['pattern' => "<theme_dir>/<area>/<theme_path>/web"])
                            ]
                        )
                    ),
                    $this->simpleFactory->create(['pattern' => $libDir]),
                ]
            ),
            new Composite(
                [
                    new Theme(
                        new Composite(
                            [
                                $this->simpleFactory->create(
                                    [
                                        'pattern' =>
                                            "<theme_dir>/<area>/<theme_path>/<namespace>_<module>/web/i18n/<locale>",
                                        'optionalParams' => ['locale'],
                                    ]
                                ),
                                $this->simpleFactory->create(
                                    ['pattern' => "<theme_dir>/<area>/<theme_path>/<namespace>_<module>/web"]
                                ),
                            ]
                        )
                    ),
                    $this->simpleFactory->create(
                        [
                            'pattern' => "<module_dir>/<namespace>/<module>/view/<area>/web/i18n/<locale>",
                            'optionalParams' => ['locale']
                        ]
                    ),
                    $this->simpleFactory->create(
                        [
                            'pattern' => "<module_dir>/<namespace>/<module>/view/base/web/i18n/<locale>",
                            'optionalParams' => ['locale']
                        ]
                    ),
                    $this->simpleFactory->create(['pattern' => "<module_dir>/<namespace>/<module>/view/<area>/web"]),
                    $this->simpleFactory->create(['pattern' => "<module_dir>/<namespace>/<module>/view/base/web"]),
                ]
            )
        );
    }

    /**
     * Retrieve newly created fallback rule for email templates.
     *
     * Emails are only loaded in a modular context, so a non-modular rule is not specified.
     *
     * @return RuleInterface
     */
    protected function createEmailTemplateFileRule()
    {
        return new Composite(
            [
                new Theme($this->simpleFactory->create(
                    ['pattern' => "<theme_dir>/<area>/<theme_path>/<namespace>_<module>/email"]
                )),
                $this->simpleFactory->create(['pattern' => "<module_dir>/<namespace>/<module>/view/<area>/email"]),
            ]
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
            case self::TYPE_EMAIL_TEMPLATE:
                $rule = $this->createEmailTemplateFileRule();
                break;
            default:
                throw new \InvalidArgumentException("Fallback rule '$type' is not supported");
        }
        $this->rules[$type] = $rule;
        return $this->rules[$type];
    }
}
