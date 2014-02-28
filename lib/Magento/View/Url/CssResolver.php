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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Url;

/**
 * Helper to work with CSS files
 */
class CssResolver
{
    /**
     * PCRE that matches non-absolute URLs in CSS content
     */
    const REGEX_CSS_RELATIVE_URLS
        = '#url\s*\(\s*(?(?=\'|").)(?!http\://|https\://|/|data\:)(.+?)(?:[\#\?].*?|[\'"])?\s*\)#';

    /**
     * File system
     *
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    /**
     * View file system
     *
     * @var \Magento\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * Constructor
     *
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\FileSystem $viewFileSystem
     */
    public function __construct(
        \Magento\App\Filesystem $filesystem,
        \Magento\View\FileSystem $viewFileSystem
    ) {
        $this->filesystem = $filesystem;
        $this->viewFileSystem = $viewFileSystem;
    }

    /**
     * Replace relative URLs
     *
     * Go through CSS content and modify relative urls, when content is read at $originalPath and then put to $newPath
     *
     * @param string $cssContent
     * @param string $originalPath
     * @param string $newPath
     * @param callable|null $cbRelUrlToPublicPath Optional custom callback to resolve relative urls to file paths
     * @return string
     */
    public function replaceCssRelativeUrls($cssContent, $originalPath, $newPath, $cbRelUrlToPublicPath = null)
    {
        $relativeUrls = $this->_extractCssRelativeUrls($cssContent);
        foreach ($relativeUrls as $urlNotation => $originalRelativeUrl) {
            if ($cbRelUrlToPublicPath) {
                $filePath = call_user_func($cbRelUrlToPublicPath, $originalRelativeUrl);
            } else {
                $filePath = dirname($originalPath) . '/' . $originalRelativeUrl;
            }
            $filePath = $this->viewFileSystem->normalizePath(str_replace('\\', '/', $filePath));
            $relativePath = $this->_getFileRelativePath(str_replace('\\', '/', $newPath), $filePath);
            $urlNotationNew = str_replace($originalRelativeUrl, $relativePath, $urlNotation);
            $cssContent = str_replace($urlNotation, $urlNotationNew, $cssContent);
        }
        return $cssContent;
    }

    /**
     * Extract non-absolute URLs from a CSS content
     *
     * @param string $cssContent
     * @return array
     */
    protected function _extractCssRelativeUrls($cssContent)
    {
        preg_match_all(self::REGEX_CSS_RELATIVE_URLS, $cssContent, $matches);
        if (!empty($matches[0]) && !empty($matches[1])) {
            return array_combine($matches[0], $matches[1]);
        }
        return array();
    }

    /**
     * Calculate relative path from a public file to another public file
     *
     * Example: public file to public file:
     *     pub/cache/_merged/hash.css -> pub/static/frontend/default/default/images/image.png
     *   Result: ../../frontend/default/default/images/image.png
     *
     * @param string $file Normalized absolute path to the file, which references $referencedFile
     * @param string $referencedFile Normalized absolute  path to the referenced file
     * @return string
     * @throws \Magento\Exception
     */
    protected function _getFileRelativePath($file, $referencedFile)
    {
        /**
         * We would like to properly calculate url relations, and do it for public files only.
         * However, directory locations are not related to each other and to any of their urls.
         * Thus, calculating relative path is not possible in general case. So we just assume,
         * that urls follow the structure of directory paths.
         */
        $topDir = $this->filesystem->getPath(\Magento\App\Filesystem::ROOT_DIR);
        if (strpos($file, $topDir) !== 0 || strpos($referencedFile, $topDir) !== 0) {
            throw new \Magento\Exception('Offset can be calculated for internal resources only.');
        }

        $offset = '';
        $currentDir = dirname($file);
        while (strpos($referencedFile, $currentDir . '/') !== 0) {
            $currentDir = dirname($currentDir);
            $offset .= '../';
        }
        $suffix = substr($referencedFile, strlen($currentDir) + 1);
        return $offset . $suffix;
    }
}
