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

namespace Magento\Css\PreProcessor\Cache\Import;

use Magento\Filesystem;

/**
 * Import entity
 */
class ImportEntity implements ImportEntityInterface
{
    /**
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * @var string
     */
    protected $originalFile;

    /**
     * @var int
     */
    protected $originalMtime;

    /**
     * @param Filesystem $filesystem
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param string $filePath
     * @param array $params
     */
    public function __construct(
        Filesystem $filesystem,
        \Magento\View\FileSystem $viewFileSystem,
        $filePath,
        array $params
    ) {
        $this->initRootDir($filesystem);

        // @todo dependency from filesystem should be removed
        $absoluteFilePath = $viewFileSystem->getViewFile($filePath, $params);
        $relativePath = $this->rootDirectory->getRelativePath($absoluteFilePath);

        $this->originalFile = $relativePath;
        $this->originalMtime = $this->rootDirectory->stat($relativePath)['mtime'];
    }

    /**
     * @return string
     */
    public function getOriginalFile()
    {
        return $this->originalFile;
    }

    /**
     * @return int
     */
    public function getOriginalMtime()
    {
        return $this->originalMtime;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!$this->isFileExist($this->getOriginalFile())) {
            return false;
        }
        $originalFileMTime = $this->rootDirectory->stat($this->getOriginalFile())['mtime'];
        return $originalFileMTime == $this->getOriginalMtime();
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function isFileExist($filePath)
    {
        return $this->rootDirectory->isFile($filePath);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['originalFile', 'originalMtime'];
    }

    /**
     * @return void
     */
    public function __wakeup()
    {
        $filesystem = \Magento\App\ObjectManager::getInstance()->get('Magento\Filesystem');
        $this->initRootDir($filesystem);
    }

    /**
     * @param Filesystem $filesystem
     * @return $this
     */
    protected function initRootDir(\Magento\Filesystem $filesystem)
    {
        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
        return $this;
    }
}
