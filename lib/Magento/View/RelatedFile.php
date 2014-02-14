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

namespace Magento\View;

/**
 * File path resolver
 */
class RelatedFile
{
    /**
     * @var Service
     */
    protected $viewService;

    /**
     * @var FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Filesystem
     */
    protected $filesystem;

    /**
     * @param Service $viewService
     * @param FileSystem $viewFileSystem
     * @param \Magento\App\Filesystem $filesystem
     */
    public function __construct(
        Service $viewService,
        FileSystem $viewFileSystem,
        \Magento\App\Filesystem $filesystem
    ) {
        $this->viewService = $viewService;
        $this->viewFileSystem = $viewFileSystem;
        $this->filesystem = $filesystem;
    }

    /**
     * Get relative $fileUrl based on information about parent file path and name.
     *
     * @param string $relatedFilePath URL to the file that was extracted from $parentPath
     * @param string $parentPath path to the file
     * @param string $parentRelativePath original file name identifier that was requested for processing
     * @param array $params theme/module parameters array
     * @return string
     */
    public function buildPath($relatedFilePath, $parentPath, $parentRelativePath, &$params)
    {
        if (strpos($relatedFilePath, \Magento\View\Service::SCOPE_SEPARATOR)) {
            $filePath = $this->viewService->extractScope(
                $this->viewFileSystem->normalizePath($relatedFilePath),
                $params
            );
        } else {
            /* Check if module file overridden on theme level based on _module property and file path */
            $themesPath = $this->filesystem->getPath(\Magento\App\Filesystem::THEMES_DIR);
            if ($params['module'] && strpos($parentPath, $themesPath) === 0) {
                /* Add module directory to relative URL */
                $filePath = dirname($params['module'] . '/' . $parentRelativePath) . '/' . $relatedFilePath;
                if (strpos($filePath, $params['module']) === 0) {
                    $filePath = ltrim(str_replace($params['module'], '', $filePath), '/');
                } else {
                    $params['module'] = false;
                }
            } else {
                $filePath = dirname($parentRelativePath) . '/' . $relatedFilePath;
            }
        }

        return $this->viewFileSystem->normalizePath($filePath);
    }
}
