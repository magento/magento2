<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\MergeStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset;

/**
 * The actual merging service
 * @since 2.0.0
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
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\View\Url\CssResolver
     * @since 2.0.0
     */
    private $cssUrlResolver;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Url\CssResolver $cssUrlResolver
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Url\CssResolver $cssUrlResolver
    ) {
        $this->filesystem = $filesystem;
        $this->cssUrlResolver = $cssUrlResolver;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function merge(array $assetsToMerge, Asset\LocalInterface $resultAsset)
    {
        $mergedContent = $this->composeMergedContent($assetsToMerge, $resultAsset);
        $filePath = $resultAsset->getPath();
        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmpDir->writeFile($filePath, $mergedContent);
        $tmpDir->renameFile($filePath, $filePath, $staticDir);
    }

    /**
     * Merge files together and modify content if needed
     *
     * @param \Magento\Framework\View\Asset\MergeableInterface[] $assetsToMerge
     * @param \Magento\Framework\View\Asset\LocalInterface $resultAsset
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    private function composeMergedContent(array $assetsToMerge, Asset\LocalInterface $resultAsset)
    {
        $result = [];
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getFilesContentDelimiter($contentType)
    {
        if ($contentType == 'js') {
            return self::MERGE_DELIMITER_JS;
        }
        return self::MERGE_DELIMITER_EMPTY;
    }
}
