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

namespace Magento\View\Design\FileResolution\Strategy;

use Magento\App\Filesystem;
use Magento\View\Design\Fallback\Factory;
use Magento\View\Design\Fallback\Rule\RuleInterface;
use Magento\View\Design\ThemeInterface;
use Magento\Filesystem\Directory\Read;

/**
 * Class Fallback
 *
 * Resolver, which performs full search of files, according to fallback rules
 */
class Fallback implements FileInterface, LocaleInterface, ViewInterface
{
    /**
     * Fallback factory
     *
     * @var Factory
     */
    protected $fallbackFactory;

    /**
     * Rule file
     *
     * @var RuleInterface
     */
    protected $ruleFile;

    /**
     * Rule locale file
     *
     * @var RuleInterface
     */
    protected $ruleLocaleFile;

    /**
     * Rule view file
     *
     * @var RuleInterface
     */
    protected $ruleViewFile;

    /**
     * Root directory with read access
     *
     * @var Read
     */
    protected $rootDirectory;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Factory $fallbackFactory
     */
    public function __construct(Filesystem $filesystem, Factory $fallbackFactory)
    {
        $this->rootDirectory = $filesystem->getDirectoryRead(Filesystem::ROOT_DIR);
        $this->fallbackFactory = $fallbackFactory;
    }

    /**
     * Get existing file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        $params = array('area' => $area, 'theme' => $themeModel, 'namespace' => null, 'module' => null);
        if ($module) {
            list($params['namespace'], $params['module']) = explode('_', $module, 2);
        }
        return $this->resolveFile($this->getFileRule(), $file, $params);
    }

    /**
     * Get locale file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @return string
     */
    public function getLocaleFile($area, ThemeInterface $themeModel, $locale, $file)
    {
        $params = array('area' => $area, 'theme' => $themeModel, 'locale' => $locale);
        return $this->resolveFile($this->getLocaleFileRule(), $file, $params);
    }

    /**
     * Get theme file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getViewFile($area, ThemeInterface $themeModel, $locale, $file, $module = null)
    {
        $params = array(
            'area' => $area, 'theme' => $themeModel, 'locale' => $locale, 'namespace' => null, 'module' => null
        );
        if ($module) {
            list($params['namespace'], $params['module']) = explode('_', $module, 2);
        }
        return $this->resolveFile($this->getViewFileRule(), $file, $params);
    }

    /**
     * Retrieve fallback rule for dynamic view files
     *
     * @return RuleInterface
     */
    protected function getFileRule()
    {
        if (!$this->ruleFile) {
            $this->ruleFile = $this->fallbackFactory->createFileRule();
        }
        return $this->ruleFile;
    }

    /**
     * Retrieve fallback rule for locale files
     *
     * @return RuleInterface
     */
    protected function getLocaleFileRule()
    {
        if (!$this->ruleLocaleFile) {
            $this->ruleLocaleFile = $this->fallbackFactory->createLocaleFileRule();
        }
        return $this->ruleLocaleFile;
    }

    /**
     * Retrieve fallback rule for static view files
     *
     * @return RuleInterface
     */
    protected function getViewFileRule()
    {
        if (!$this->ruleViewFile) {
            $this->ruleViewFile = $this->fallbackFactory->createViewFileRule();
        }
        return $this->ruleViewFile;
    }

    /**
     * Get path of file after using fallback rules
     *
     * @param RuleInterface $fallbackRule
     * @param string $file
     * @param array $params
     * @return string
     */
    protected function resolveFile(RuleInterface $fallbackRule, $file, $params = array())
    {
        $path = '';
        foreach ($fallbackRule->getPatternDirs($params) as $dir) {
            $path = "{$dir}/{$file}";
            if ($this->rootDirectory->isExist($this->rootDirectory->getRelativePath($path))) {
                return $path;
            }
        }
        return $path;
    }
}
