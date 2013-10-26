<?php
/**
 * Helper to work with CSS files
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Helper;

class Css
{
    /**
     * PCRE that matches non-absolute URLs in CSS content
     */
    const REGEX_CSS_RELATIVE_URLS
        = '#url\s*\(\s*(?(?=\'|").)(?!http\://|https\://|/|data\:)(.+?)(?:[\#\?].*?|[\'"])?\s*\)#';

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirs;

    /**
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\Dir $dirs
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\App\Dir $dirs
    ) {
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
    }

    /**
     * Go through CSS content and modify relative urls, when content is read at $originalPath and then put to $newPath
     *
     * @param string $cssContent
     * @param string $originalPath
     * @param string $newPath
     * @param callable|null $cbRelUrlToPublicPath Optional custom callback to resolve relative urls to file paths
     * @return mixed
     */
    public function replaceCssRelativeUrls($cssContent, $originalPath, $newPath, $cbRelUrlToPublicPath = null)
    {
        $newPath = $this->_filesystem->normalizePath($newPath);
        $relativeUrls = $this->_extractCssRelativeUrls($cssContent);
        foreach ($relativeUrls as $urlNotation => $originalRelativeUrl) {
            if ($cbRelUrlToPublicPath) {
                $filePath = call_user_func($cbRelUrlToPublicPath, $originalRelativeUrl, $originalPath);
            } else {
                $filePath = $this->_filesystem->normalizePath(dirname($originalPath) . '/' . $originalRelativeUrl);
            }
            $filePath = $this->_filesystem->normalizePath($filePath);
            $relativePath = $this->_getFileRelativePath($newPath, $filePath);
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
     * @throws \Magento\Core\Exception
     */
    protected function _getFileRelativePath($file, $referencedFile)
    {
        /**
         * We would like to properly calculate url relations, and do it for public files only.
         * However, directory locations are not related to each other and to any of their urls.
         * Thus, calculating relative path is not possible in general case. So we just assume,
         * that urls follow the structure of directory paths.
         */
        $topDir = $this->_dirs->getDir(\Magento\App\Dir::ROOT);
        $topDir = $this->_filesystem->normalizePath($topDir);
        if (strpos($file, $topDir) !== 0 || strpos($referencedFile, $topDir) !== 0) {
            throw new \Magento\Core\Exception('Offset can be calculated for internal resources only.');
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
