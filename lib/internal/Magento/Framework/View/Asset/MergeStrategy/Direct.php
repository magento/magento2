<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\MergeStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Asset;
use Magento\Framework\View\Asset\MergeStrategyInterface;
use Magento\Framework\View\Url\CssResolver;

/**
 * The actual merging service
 */
class Direct implements MergeStrategyInterface
{
    /**
     * Delimiters for merging files of various content type
     */
    private const MERGE_DELIMITER_JS = ';';

    private const MERGE_DELIMITER_EMPTY = '';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CssResolver
     */
    private $cssUrlResolver;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @param Filesystem $filesystem
     * @param CssResolver $cssUrlResolver
     * @param Random|null $mathRandom
     */
    public function __construct(
        Filesystem $filesystem,
        CssResolver $cssUrlResolver,
        ?Random $mathRandom = null
    ) {
        $this->filesystem = $filesystem;
        $this->cssUrlResolver = $cssUrlResolver;
        $this->mathRandom = $mathRandom ?: ObjectManager::getInstance()->get(Random::class);
    }

    /**
     * @inheritdoc
     */
    public function merge(array $assetsToMerge, Asset\LocalInterface $resultAsset)
    {
        $mergedContent = $this->composeMergedContent($assetsToMerge, $resultAsset);
        $filePath = $resultAsset->getPath();
        $tmpFilePath = $filePath . $this->mathRandom->getUniqueHash('_');
        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $staticDir->writeFile($tmpFilePath, $mergedContent);
        $staticDir->renameFile($tmpFilePath, $filePath, $staticDir);
    }

    /**
     * Merge files together and modify content if needed
     *
     * @param array $assetsToMerge
     * @param Asset\LocalInterface $resultAsset
     * @return array|string
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
