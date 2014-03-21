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

/**
 * Minification strategy that generates minified file, if it does not exist or outdated
 */
namespace Magento\Code\Minifier\Strategy;

use Magento\Filesystem\Directory\Read;
use Magento\Filesystem\Directory\Write;

class Generate implements \Magento\Code\Minifier\StrategyInterface
{
    /**
     * @var \Magento\Code\Minifier\AdapterInterface
     */
    protected $adapter;

    /**
     * @var Read
     */
    protected $rootDirectory;

    /**
     * @var Write
     */
    protected $pubViewCacheDir;

    /**
     * @param \Magento\Code\Minifier\AdapterInterface $adapter
     * @param \Magento\App\Filesystem $filesystem
     */
    public function __construct(\Magento\Code\Minifier\AdapterInterface $adapter, \Magento\App\Filesystem $filesystem)
    {
        $this->adapter = $adapter;
        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
        $this->pubViewCacheDir = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::PUB_VIEW_CACHE_DIR);
    }

    /**
     * Get path to minified file for specified original file
     *
     * @param string $originalFile path to original file relative to pub/view_cache
     * @param string $targetFile path relative to pub/view_cache
     * @return void
     */
    public function minifyFile($originalFile, $targetFile)
    {
        if ($this->_isUpdateNeeded($originalFile, $targetFile)) {
            $content = $this->rootDirectory->readFile($originalFile);
            $content = $this->adapter->minify($content);
            $targetFile = $this->pubViewCacheDir->getRelativePath($targetFile);
            $this->pubViewCacheDir->writeFile($targetFile, $content);
            $this->pubViewCacheDir->touch($targetFile, $this->rootDirectory->stat($originalFile)['mtime']);
        }
    }

    /**
     * Check whether minified file should be created/updated
     *
     * @param string $originalFile path to original file relative to pub/view_cache
     * @param string $minifiedFile path relative to pub/view_cache
     * @return bool
     */
    protected function _isUpdateNeeded($originalFile, $minifiedFile)
    {
        if (!$this->pubViewCacheDir->isExist($minifiedFile)) {
            return true;
        }
        $originalFileMtime = $this->rootDirectory->stat($originalFile)['mtime'];
        $minifiedFileMtime = $this->pubViewCacheDir->stat($minifiedFile)['mtime'];
        return $originalFileMtime != $minifiedFileMtime;
    }
}
