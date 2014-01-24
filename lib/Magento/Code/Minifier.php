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

namespace Magento\Code;

use Magento\Filesystem\Directory\Read;

class Minifier
{
    /**
     * @var \Magento\Code\Minifier\StrategyInterface
     */
    private $_strategy;

    /**
     * @var Read
     */
    private $rootDirectory;

    /**
     * @var string directory name where minified files are saved
     */
    private $directoryName;

    /**
     * @var Read
     */
    private $pubViewCacheDir;

    /**
     * @param \Magento\Code\Minifier\StrategyInterface $strategy
     * @param \Magento\App\Filesystem $filesystem
     * @param string $directoryName
     */
    public function __construct(
        \Magento\Code\Minifier\StrategyInterface $strategy,
        \Magento\App\Filesystem $filesystem,
        $directoryName
    ) {
        $this->_strategy = $strategy;
        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
        $this->pubViewCacheDir = $filesystem->getDirectoryRead(\Magento\App\Filesystem::PUB_VIEW_CACHE_DIR);
        $this->directoryName = $directoryName;
    }

    /**
     * Get path to minified file
     *
     * @param string $originalFile
     * @return bool|string
     */
    public function getMinifiedFile($originalFile)
    {
        if ($this->_isFileMinified($originalFile)) {
            return $originalFile;
        }
        $originalFileRelative = $this->rootDirectory->getRelativePath($originalFile);
        $minifiedFile = $this->_findOriginalMinifiedFile($originalFileRelative);
        if (!$minifiedFile) {
            $minifiedFile = $this->directoryName . '/' . $this->_generateMinifiedFileName($originalFile);
            $this->_strategy->minifyFile($originalFileRelative, $minifiedFile);
        }

        $minifiedFile = $this->pubViewCacheDir->getRelativePath($minifiedFile);
        return $this->pubViewCacheDir->getAbsolutePath($minifiedFile);
    }

    /**
     * Check if file is minified
     *
     * @param string $fileName
     * @return bool
     */
    protected function _isFileMinified($fileName)
    {
        return (bool)preg_match('#.min.\w+$#', $fileName);
    }

    /**
     * Generate name of the minified file
     *
     * @param string $originalFile
     * @return string
     */
    protected function _generateMinifiedFileName($originalFile)
    {
        $fileInfo = pathinfo($originalFile);
        $minifiedName = md5($originalFile) . '_' . $fileInfo['filename'] . '.min.' . $fileInfo['extension'];

        return $minifiedName;
    }

    /**
     * Search for minified file provided along with the original file in the code base
     *
     * @param string $originalFile
     * @return bool|string
     */
    protected function _findOriginalMinifiedFile($originalFile)
    {
        $fileInfo = pathinfo($originalFile);
        $minifiedFile = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.min.' . $fileInfo['extension'];
        if ($this->rootDirectory->isExist($minifiedFile)) {
            return $minifiedFile;
        }
        return false;
    }
}
