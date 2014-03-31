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
namespace Magento\View\Asset\MergeStrategy;

/**
 * Simple merge strategy - merge anyway
 */
class Direct implements \Magento\View\Asset\MergeStrategyInterface
{
    /**#@+
     * Delimiters for merging files of various content type
     */
    const MERGE_DELIMITER_JS = ';';

    const MERGE_DELIMITER_EMPTY = '';

    /**#@-*/

    /**
     * Directory Write
     *
     * @var \Magento\Filesystem\Directory\Write
     */
    private $_directory;

    /**
     * Css Resolver
     *
     * @var \Magento\View\Url\CssResolver
     */
    protected $cssUrlResolver;

    /**
     * Constructor
     *
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\Url\CssResolver $cssUrlResolver
     */
    public function __construct(\Magento\App\Filesystem $filesystem, \Magento\View\Url\CssResolver $cssUrlResolver)
    {
        $this->_directory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::PUB_DIR);
        $this->_cssUrlResolver = $cssUrlResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeFiles(array $publicFiles, $destinationFile, $contentType)
    {
        $mergedContent = $this->composeMergedContent($publicFiles, $destinationFile, $contentType);
        $this->_directory->writeFile($this->_directory->getRelativePath($destinationFile), $mergedContent);
    }

    /**
     * Merge files together and modify content if needed
     *
     * @param array $publicFiles
     * @param string $targetFile
     * @param string $contentType
     * @return string
     * @throws \Magento\Exception
     */
    protected function composeMergedContent(array $publicFiles, $targetFile, $contentType)
    {
        $result = array();
        $isCss = $contentType == \Magento\View\Publisher::CONTENT_TYPE_CSS ? true : false;
        $delimiter = $this->_getFilesContentDelimiter($contentType);

        foreach ($publicFiles as $file) {
            if (!$this->_directory->isExist($this->_directory->getRelativePath($file))) {
                throw new \Magento\Exception("Unable to locate file '{$file}' for merging.");
            }
            $content = $this->_directory->readFile($this->_directory->getRelativePath($file));
            if ($isCss) {
                $content = $this->_cssUrlResolver->replaceCssRelativeUrls($content, $file, $targetFile);
            }
            $result[] = $content;
        }
        $result = ltrim(implode($delimiter, $result));
        if ($isCss) {
            $result = $this->_popCssImportsUp($result);
        }

        return $result;
    }

    /**
     * Put CSS import directives to the start of CSS content
     *
     * @param string $contents
     * @return string
     */
    protected function _popCssImportsUp($contents)
    {
        $parts = preg_split('/(@import\s.+?;\s*)/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
        $imports = array();
        $css = array();
        foreach ($parts as $part) {
            if (0 === strpos($part, '@import', 0)) {
                $imports[] = trim($part);
            } else {
                $css[] = $part;
            }
        }

        $result = implode($css);
        if ($imports) {
            $result = implode("\n", $imports) . "\n" . "/* Import directives above popped up. */\n" . $result;
        }
        return $result;
    }

    /**
     * Return delimiter for separation of merged files content
     *
     * @param string $contentType
     * @return string
     */
    protected function _getFilesContentDelimiter($contentType)
    {
        if ($contentType == \Magento\View\Publisher::CONTENT_TYPE_JS) {
            return self::MERGE_DELIMITER_JS;
        }
        return self::MERGE_DELIMITER_EMPTY;
    }
}
