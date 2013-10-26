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
namespace Magento\Core\Model\Page\Asset\MergeStrategy;

class Direct implements \Magento\Core\Model\Page\Asset\MergeStrategyInterface
{
    /**
     * @var \Magento\Filesystem
     */
    private $_filesystem;

    /**
     * @var \Magento\App\Dir
     */
    private $_dirs;

    /**
     * @var \Magento\Core\Helper\Css
     */
    private $_cssHelper;

    /**
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\Dir $dirs
     * @param \Magento\Core\Helper\Css $cssHelper
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\App\Dir $dirs,
        \Magento\Core\Helper\Css $cssHelper
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
     * @throws \Magento\Exception
     */
    protected function _composeMergedContent(array $publicFiles, $targetFile, $contentType)
    {
        $result = array();
        $isCss = $contentType == \Magento\Core\Model\View\Publisher::CONTENT_TYPE_CSS;

        foreach ($publicFiles as $file) {
            if (!$this->_filesystem->has($file)) {
                throw new \Magento\Exception("Unable to locate file '{$file}' for merging.");
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
