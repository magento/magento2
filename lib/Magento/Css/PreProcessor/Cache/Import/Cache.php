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
namespace Magento\Css\PreProcessor\Cache\Import;

use Magento\Exception;
use Magento\App\Filesystem;

/**
 * File cache entity for entry file
 */
class Cache implements \Magento\Css\PreProcessor\Cache\CacheInterface
{
    /**
     * Cache import type
     */
    const IMPORT_CACHE = 'import';

    /**
     * @var array
     */
    protected $importEntities = array();

    /**
     * @var null|\Magento\View\Publisher\FileInterface
     */
    protected $cachedFile;

    /**
     * @var string
     */
    protected $uniqueFileKey;

    /**
     * @var Map\Storage
     */
    protected $storage;

    /**
     * @var ImportEntityFactory
     */
    protected $importEntityFactory;

    /**
     * @var \Magento\View\Publisher\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $readDirectory;

    /**
     * @param Map\Storage $storage
     * @param ImportEntityFactory $importEntityFactory
     * @param Filesystem $filesystem
     * @param \Magento\View\Publisher\FileInterface $publisherFile
     * @param \Magento\View\Publisher\FileFactory $fileFactory
     */
    public function __construct(
        Map\Storage $storage,
        ImportEntityFactory $importEntityFactory,
        Filesystem $filesystem,
        \Magento\View\Publisher\FileInterface $publisherFile,
        \Magento\View\Publisher\FileFactory $fileFactory
    ) {
        $this->storage = $storage;
        $this->fileFactory = $fileFactory;
        $this->readDirectory = $filesystem->getDirectoryRead(Filesystem::ROOT_DIR);
        $this->importEntityFactory = $importEntityFactory;
        $this->uniqueFileKey = $this->prepareKey($publisherFile);

        $this->loadImportEntities();
    }

    /**
     * Clear storage for current cached file
     *
     * @return $this
     */
    public function clear()
    {
        $this->cachedFile = null;
        $this->importEntities = array();
        $this->storage->delete($this->uniqueFileKey);
        return $this;
    }

    /**
     * Return cached file
     *
     * @return null|\Magento\View\Publisher\FileInterface
     */
    public function get()
    {
        if ($this->cachedFile instanceof \Magento\View\Publisher\FileInterface) {
            return $this->cachedFile;
        }
        return null;
    }

    /**
     * Add file to cache
     *
     * @param \Magento\Less\PreProcessor\File\Less $lessFile
     * @return $this
     */
    public function add($lessFile)
    {
        $this->importEntities[$lessFile->getFileIdentifier()] = $this->importEntityFactory->create($lessFile);
        return $this;
    }

    /**
     * Save state of files
     *
     * @param \Magento\View\Publisher\FileInterface $cachedFile
     * @return $this
     */
    public function save($cachedFile)
    {
        $this->storage->save($this->uniqueFileKey, $this->prepareSaveData($cachedFile));
        return $this;
    }

    /**
     * Prepare cache key for publication file
     *
     * @param \Magento\View\Publisher\FileInterface $lessFile
     * @return string
     */
    protected function prepareKey($lessFile)
    {
        $params = $lessFile->getViewParams();
        if (!empty($params['themeModel'])) {
            $themeModel = $params['themeModel'];
            $params['themeModel'] = $themeModel->getId() ?: md5($themeModel->getThemePath());
        }
        ksort($params);
        return $lessFile->getFilePath() . '|' . implode('|', $params);
    }

    /**
     * Load state of files
     *
     * @return $this
     */
    protected function loadImportEntities()
    {
        $importEntities = unserialize($this->storage->load($this->uniqueFileKey));
        $this->cachedFile = isset($importEntities['cached_file']) ? $importEntities['cached_file'] : null;
        $this->importEntities = isset($importEntities['imports']) ? $importEntities['imports'] : array();
        if (!$this->isValid()) {
            $this->clear();
        }
        return $this;
    }

    /**
     * Check file change time to make sure that file wasn't changed and it doesn't need of pre-processing
     *
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->importEntities)) {
            return false;
        }
        /** @var ImportEntity $entity */
        foreach ($this->importEntities as $entity) {
            $fileSourcePath = $entity->getOriginalFile();
            $fileMtime = $this->readDirectory->stat($this->readDirectory->getRelativePath($fileSourcePath))['mtime'];
            if ($fileMtime !== $entity->getOriginalMtime()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Serialize data of files state
     *
     * @param \Magento\View\Publisher\FileInterface $cachedFile
     * @return string
     */
    protected function prepareSaveData($cachedFile)
    {
        return serialize(array('cached_file' => clone $cachedFile, 'imports' => $this->importEntities));
    }
}
