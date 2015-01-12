<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback;
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
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
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
    protected function resolveFile(RuleInterface $fallbackRule, $file, array $params = [])
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
