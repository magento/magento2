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
namespace Magento\Framework\View\Asset\MergeStrategy;

use Magento\Framework\View\Asset;

/**
 * The actual merging service
 */
class Direct implements \Magento\Framework\View\Asset\MergeStrategyInterface
{
    /**#@+
     * Delimiters for merging files of various content type
     */
    const MERGE_DELIMITER_JS = ';';

    const MERGE_DELIMITER_EMPTY = '';

    /**#@-*/

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\View\Url\CssResolver
     */
    private $cssUrlResolver;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\View\Url\CssResolver $cssUrlResolver
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\View\Url\CssResolver $cssUrlResolver
    ) {
        $this->filesystem = $filesystem;
        $this->cssUrlResolver = $cssUrlResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(array $assetsToMerge, Asset\LocalInterface $resultAsset)
    {
        $mergedContent = $this->composeMergedContent($assetsToMerge, $resultAsset);
        $dir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR);
        $dir->writeFile($resultAsset->getPath(), $mergedContent);
    }

    /**
     * Merge files together and modify content if needed
     *
     * @param \Magento\Framework\View\Asset\MergeableInterface[] $assetsToMerge
     * @param \Magento\Framework\View\Asset\LocalInterface $resultAsset
     * @return string
     * @throws \Magento\Framework\Exception
     */
    private function composeMergedContent(array $assetsToMerge, Asset\LocalInterface $resultAsset)
    {
        $result = array();
        /** @var Asset\MergeableInterface $asset */
        foreach ($assetsToMerge as $asset) {
            $result[] = $this->preProcessBeforeMerging($asset, $resultAsset, $asset->getContent());
        }
        $delimiter = $this->_getFilesContentDelimiter($resultAsset->getContentType());
        $result = $this->preProcessMergeResult($resultAsset, ltrim(implode($delimiter, $result)));
        return $result;
    }

    /**
     * Process an asset before merging into resulting asset
     *
     * @param Asset\LocalInterface $item
     * @param Asset\LocalInterface $result
     * @param string $content
     * @return string
     */
    private function preProcessBeforeMerging(Asset\LocalInterface $item, Asset\LocalInterface $result, $content)
    {
        if ($result->getContentType() == 'css') {
            $from = $item->getPath();
            $to = $result->getPath();
            return $this->cssUrlResolver->relocateRelativeUrls($content, $from, $to);
        }
        return $content;
    }

    /**
     * Process the resulting asset after merging content is done
     *
     * @param Asset\LocalInterface $result
     * @param string $content
     * @return string
     */
    private function preProcessMergeResult(Asset\LocalInterface $result, $content)
    {
        if ($result->getContentType() == 'css') {
            $content = $this->cssUrlResolver->aggregateImportDirectives($content);
        }
        return $content;
    }

    /**
     * Return delimiter for separation of merged files content
     *
     * @param string $contentType
     * @return string
     */
    protected function _getFilesContentDelimiter($contentType)
    {
        if ($contentType == 'js') {
            return self::MERGE_DELIMITER_JS;
        }
        return self::MERGE_DELIMITER_EMPTY;
    }
}
