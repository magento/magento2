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
namespace Magento\Less\File\Source;

use Magento\View\Layout\File\SourceInterface;
use Magento\View\Design\ThemeInterface;
use Magento\App\Filesystem;
use Magento\Filesystem\Directory\ReadInterface;
use Magento\View\Layout\File\Factory;
use Magento\View\Layout\File\FileList\Factory as FileListFactory;

/**
 * Source of base layout files introduced by modules
 */
class Library implements SourceInterface
{
    /**
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @var ReadInterface
     */
    protected $libraryDirectory;

    /**
     * @var ReadInterface
     */
    protected $themesDirectory;

    /**
     * @var FileListFactory
     */
    protected $fileListFactory;

    /**
     * @param FileListFactory $fileListFactory
     * @param Filesystem $filesystem
     * @param Factory $fileFactory
     */
    public function __construct(FileListFactory $fileListFactory, Filesystem $filesystem, Factory $fileFactory)
    {
        $this->fileListFactory = $fileListFactory;
        $this->libraryDirectory = $filesystem->getDirectoryRead(Filesystem::PUB_LIB_DIR);
        $this->themesDirectory = $filesystem->getDirectoryRead(Filesystem::THEMES_DIR);
        $this->fileFactory = $fileFactory;
    }

    /**
     * Retrieve files
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return array|\Magento\View\Layout\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath = '*')
    {
        $filePath = pathinfo($filePath, PATHINFO_EXTENSION) ? $filePath : rtrim($filePath, '.') . '.less';
        $list = $this->fileListFactory->create();
        $files = $this->libraryDirectory->search($filePath);
        $list->add($this->createFiles($this->libraryDirectory, $theme, $files));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $themeFullPath = $currentTheme->getFullPath();
            $files = $this->themesDirectory->search("{$themeFullPath}/{$filePath}");
            $list->replace($this->createFiles($this->themesDirectory, $theme, $files), false);
        }
        return $list->getAll();
    }

    /**
     * @param ReadInterface $reader
     * @param ThemeInterface $theme
     * @param array $files
     * @return array
     */
    protected function createFiles(ReadInterface $reader, ThemeInterface $theme, $files)
    {
        $result = array();
        foreach ($files as $file) {
            $filename = $reader->getAbsolutePath($file);
            $result[] = $this->fileFactory->create($filename, false, $theme);
        }
        return $result;
    }
}
