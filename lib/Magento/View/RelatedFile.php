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
     * View service
     *
     * @var Service
     */
    protected $viewService;

    /**
     * View file system
     *
     * @var FileSystem
     */
    protected $viewFileSystem;

    /**
     * @param Service $viewService
     * @param FileSystem $viewFileSystem
     */
    public function __construct(Service $viewService, FileSystem $viewFileSystem)
    {
        $this->viewService = $viewService;
        $this->viewFileSystem = $viewFileSystem;
    }

    /**
     * Get relative $fileUrl based on information about parent file path and name.
     *
     * @param string $relativeFilePath URL to the file that was extracted from $parentPath
     * @param string $parentRelativePath original file name identifier that was requested for processing
     * @param array &$params theme/module parameters array
     * @return string
     */
    public function buildPath($relativeFilePath, $parentRelativePath, &$params)
    {
        if (strpos($relativeFilePath, \Magento\View\Service::SCOPE_SEPARATOR)) {
            $relativeFilePath = $this->viewService->extractScope($relativeFilePath, $params);
        } else {
            $relativeFilePath = dirname($parentRelativePath) . '/' . $relativeFilePath;
        }
        return $this->viewFileSystem->normalizePath($relativeFilePath);
    }
}
