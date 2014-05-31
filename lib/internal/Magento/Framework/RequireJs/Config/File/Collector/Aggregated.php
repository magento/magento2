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

namespace Magento\Framework\RequireJs\Config\File\Collector;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;

/**
 * Source of RequireJs config files basing on list of directories they may be located in
 */
class Aggregated implements CollectorInterface
{
    /**
     * Base files
     *
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $baseFiles;

    /**
     * Theme files
     *
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $themeFiles;

    /**
     * Theme modular files
     *
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $themeModularFiles;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $libDirectory;

    /**
     * @var \Magento\Framework\View\File\Factory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\View\File\Factory $fileFactory
     * @param CollectorInterface $baseFiles
     * @param CollectorInterface $themeFiles
     * @param CollectorInterface $themeModularFiles
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\View\File\Factory $fileFactory,
        CollectorInterface $baseFiles,
        CollectorInterface $themeFiles,
        CollectorInterface $themeModularFiles
    ) {
        $this->libDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::LIB_WEB);
        $this->fileFactory = $fileFactory;
        $this->baseFiles = $baseFiles;
        $this->themeFiles = $themeFiles;
        $this->themeModularFiles = $themeModularFiles;
    }

    /**
     * Get layout files from modules, theme with ancestors and library
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        if (empty($filePath)) {
            throw new \InvalidArgumentException('File path must be specified');
        }
        $files = array();
        if ($this->libDirectory->isExist($filePath)) {
            $filename = $this->libDirectory->getAbsolutePath($filePath);
            $files[] = $this->fileFactory->create($filename);
        }

        $files = array_merge($files, $this->baseFiles->getFiles($theme, $filePath));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $files = array_merge($files, $this->themeModularFiles->getFiles($currentTheme, $filePath));
            $files = array_merge($files, $this->themeFiles->getFiles($currentTheme, $filePath));
        }
        return $files;
    }
}
