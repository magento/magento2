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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Simple merge strategy - merge anyway
 */
class Mage_Core_Model_Page_Asset_MergeStrategy_Direct implements Mage_Core_Model_Page_Asset_MergeStrategyInterface
{
    /**
     * @var Magento_Filesystem
     */
    private $_filesystem;

    /**
     * @var Mage_Core_Model_Dir
     */
    private $_dirs;

    /**
     * @var Mage_Core_Helper_Css
     */
    private $_cssHelper;

    /**
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Helper_Css $cssHelper
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Helper_Css $cssHelper
    ) {
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
        $this->_cssHelper = $cssHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeFiles(array $publicFiles, $destinationFile, $contentType)
    {
        $mergedContent = $this->_composeMergedContent($publicFiles, $destinationFile, $contentType);

        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->write($destinationFile, $mergedContent);
    }

    /**
     * Merge files together and modify content if needed
     *
     * @param array $publicFiles
     * @param string $targetFile
     * @param string $contentType
     * @return string
     * @throws Magento_Exception
     */
    protected function _composeMergedContent(array $publicFiles, $targetFile, $contentType)
    {
        $result = array();
        $isCss = $contentType == Mage_Core_Model_View_Publisher::CONTENT_TYPE_CSS;

        foreach ($publicFiles as $file) {
            if (!$this->_filesystem->has($file)) {
                throw new Magento_Exception("Unable to locate file '{$file}' for merging.");
            }
            $content = $this->_filesystem->read($file);
            if ($isCss) {
                $content = $this->_cssHelper->replaceCssRelativeUrls($content, $file, $targetFile);
            }
            $result[] = $content;
        }
        $result = ltrim(implode($result));
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
}
