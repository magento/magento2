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
 * \Iterator that aggregates one or more assets and provides a single public file with equivalent behavior
 */
namespace Magento\Core\Model\Page\Asset;

class Merged implements \Iterator
{
    /**
     * Sub path for merged files relative to public view cache directory
     */
    const PUBLIC_MERGE_DIR  = '_merged';

    /**
     * @var \Magento\ObjectManager
     */
    private $_objectManager;

    /**
     * @var \Magento\Core\Model\Logger
     */
    private $_logger;

    /**
     * @var \Magento\Core\Model\Page\Asset\MergeStrategyInterface
     */
    private $_mergeStrategy;

    /**
     * @var \Magento\Core\Model\Page\Asset\MergeableInterface[]
     */
    private $_assets;

    /**
     * @var string
     */
    private $_contentType;

    /**
     * Whether initialization has been performed or not
     *
     * @var bool
     */
    private $_isInitialized = false;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\Logger $logger,
     * @param \Magento\App\Dir $dirs,
     * @param \Magento\Core\Model\Page\Asset\MergeStrategyInterface $mergeStrategy
     * @param array $assets
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\App\Dir $dirs,
        \Magento\Core\Model\Page\Asset\MergeStrategyInterface $mergeStrategy,
        array $assets
    ) {
        $this->_objectManager = $objectManager;
        $this->_logger = $logger;
        $this->_dirs = $dirs;
        $this->_mergeStrategy = $mergeStrategy;

        if (!$assets) {
            throw new \InvalidArgumentException('At least one asset has to be passed for merging.');
        }
        /** @var $asset \Magento\Core\Model\Page\Asset\MergeableInterface */
        foreach ($assets as $asset) {
            if (!($asset instanceof \Magento\Core\Model\Page\Asset\MergeableInterface)) {
                throw new \InvalidArgumentException(
                    'Asset has to implement \Magento\Core\Model\Page\Asset\MergeableInterface.'
                );
            }
            if (!$this->_contentType) {
                $this->_contentType = $asset->getContentType();
            } else if ($asset->getContentType() != $this->_contentType) {
                throw new \InvalidArgumentException(
                    "Content type '{$asset->getContentType()}' cannot be merged with '{$this->_contentType}'."
                );
            }
        }
        $this->_assets = $assets;
    }

    /**
     * Attempt to merge assets, falling back to original non-merged ones, if merging fails
     */
    protected function _initialize()
    {
        if (!$this->_isInitialized) {
            $this->_isInitialized = true;
            try {
                $this->_assets = array($this->_getMergedAsset($this->_assets));
            } catch (\Exception $e) {
                $this->_logger->logException($e);
            }
        }
    }

    /**
     * Retrieve asset instance representing a merged file
     *
     * @param \Magento\Core\Model\Page\Asset\MergeableInterface[] $assets
     * @return \Magento\Core\Model\Page\Asset\AssetInterface
     */
    protected function _getMergedAsset(array $assets)
    {
        $sourceFiles = $this->_getPublicFilesToMerge($assets);
        $destinationFile = $this->_getMergedFilePath($sourceFiles);

        $this->_mergeStrategy->mergeFiles($sourceFiles, $destinationFile, $this->_contentType);
        return $this->_objectManager->create('Magento\Core\Model\Page\Asset\PublicFile', array(
            'file' => $destinationFile,
            'contentType' => $this->_contentType,
        ));
    }

    /**
     * Go through all the files to merge, ensure that they are public (publish if needed), and compose
     * array of public paths to merge
     *
     * @param \Magento\Core\Model\Page\Asset\MergeableInterface[] $assets
     * @return array
     */
    protected function _getPublicFilesToMerge(array $assets)
    {
        $result = array();
        foreach ($assets as $asset) {
            $publicFile = $asset->getSourceFile();
            $result[$publicFile] = $publicFile;
        }
        return $result;
    }

    /**
     * Return file name for the resulting merged file
     *
     * @param array $publicFiles
     * @return string
     */
    protected function _getMergedFilePath(array $publicFiles)
    {
        $jsDir = \Magento\Filesystem::fixSeparator($this->_dirs->getDir(\Magento\App\Dir::PUB_LIB));
        $publicDir = \Magento\Filesystem::fixSeparator($this->_dirs->getDir(\Magento\App\Dir::STATIC_VIEW));
        $prefixRemovals = array($jsDir, $publicDir);

        $relFileNames = array();
        foreach ($publicFiles as $file) {
            $file = \Magento\Filesystem::fixSeparator($file);
            $relFileNames[] = str_replace($prefixRemovals, '', $file);
        }

        $mergedDir = $this->_dirs->getDir(\Magento\App\Dir::PUB_VIEW_CACHE) . '/'
            . self::PUBLIC_MERGE_DIR;
        return $mergedDir . '/' . md5(implode('|', $relFileNames)) . '.' . $this->_contentType;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Core\Model\Page\Asset\AssetInterface
     */
    public function current()
    {
        $this->_initialize();
        return current($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->_initialize();
        return key($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->_initialize();
        next($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->_initialize();
        reset($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->_initialize();
        return (bool)current($this->_assets);
    }
}
