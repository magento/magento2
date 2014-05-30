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

namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\App\Filesystem;
use Magento\Framework\View\Design\FileResolution\Fallback;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Resolver for view files
 */
class Simple implements Fallback\ResolverInterface
{
    /**
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * Fallback factory
     *
     * @var RulePool
     */
    protected $rulePool;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param RulePool $rulePool
     * @param Fallback\CacheDataInterface $cache
     */
    public function __construct(Filesystem $filesystem, RulePool $rulePool, Fallback\CacheDataInterface $cache)
    {
        $this->rootDirectory = $filesystem->getDirectoryRead(Filesystem::ROOT_DIR);
        $this->rulePool = $rulePool;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($type, $file, $area = null, ThemeInterface $theme = null, $locale = null, $module = null)
    {
        self::assertFilePathFormat($file);
        $themePath = $theme ? $theme->getThemePath() : '';
        $path = $this->cache->getFromCache($type, $file, $area, $themePath, $locale, $module);
        if (false !== $path) {
            $path = $path ? $this->rootDirectory->getAbsolutePath($path) : false;
        } else {
            $params = ['area' => $area, 'theme' => $theme, 'locale' => $locale];
            foreach ($params as $key => $param) {
                if ($param === null) {
                    unset($params[$key]);
                }
            }
            if (!empty($module)) {
                list($params['namespace'], $params['module']) = explode('_', $module, 2);
            }
            $path = $this->resolveFile($this->rulePool->getRule($type), $file, $params);
            $cachedValue = $path ? $this->rootDirectory->getRelativePath($path) : '';

            $this->cache->saveToCache($cachedValue, $type, $file, $area, $themePath, $locale, $module);
        }
        return $path;
    }

    /**
     * Validate the file path format
     *
     * @param string $filePath
     * @throws \InvalidArgumentException
     * @return void
     */
    public static function assertFilePathFormat($filePath)
    {
        if (strpos(str_replace('\\', '/', $filePath), './') !== false) {
            throw new \InvalidArgumentException("File path '{$filePath}' is forbidden for security reasons.");
        }
    }

    /**
     * Get path of file after using fallback rules
     *
     * @param RuleInterface $fallbackRule
     * @param string $file
     * @param array $params
     * @return string|bool
     */
    protected function resolveFile(RuleInterface $fallbackRule, $file, array $params = array())
    {
        foreach ($fallbackRule->getPatternDirs($params) as $dir) {
            $path = "{$dir}/{$file}";
            if ($this->rootDirectory->isExist($this->rootDirectory->getRelativePath($path))) {
                return $path;
            }
        }
        return false;
    }
}
