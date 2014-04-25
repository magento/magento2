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
namespace Magento\Sales\Model;

class Download
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_rootDir;

    /**
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_fileStorageDatabase;

    /**
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $_storageDatabaseFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_rootDir = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->_fileStorageDatabase = $fileStorageDatabase;
        $this->_storageDatabaseFactory = $storageDatabaseFactory;
        $this->_fileFactory = $fileFactory;
    }

    /**
     * Custom options downloader
     *
     * @param array $info
     * @return void
     * @throws \Exception
     */
    public function downloadFile($info)
    {
        $relativePath = $info['order_path'];
        if ($this->_isCanProcessed($relativePath)) {
            //try get file from quote
            $relativePath = $info['quote_path'];
            if ($this->_isCanProcessed($relativePath)) {
                throw new \Exception();
            }
        }

        $this->_fileFactory->create(
            $info['title'],
            array('value' => $this->_rootDir->getRelativePath($relativePath), 'type' => 'filename'),
            \Magento\Framework\App\Filesystem::ROOT_DIR
        );
    }

    /**
     * @param string $relativePath
     * @return bool
     */
    protected function _isCanProcessed($relativePath)
    {
        $filePath = $this->_rootDir->getAbsolutePath($relativePath);
        return (!$this->_rootDir->isFile(
            $relativePath
        ) || !$this->_rootDir->isReadable(
            $relativePath
        )) && !$this->_processDatabaseFile(
            $filePath
        );
    }

    /**
     * Check file in database storage if needed and place it on file system
     *
     * @param string $filePath
     * @return bool
     */
    protected function _processDatabaseFile($filePath)
    {
        if (!$this->_fileStorageDatabase->checkDbUsage()) {
            return false;
        }
        $relativePath = $this->_fileStorageDatabase->getMediaRelativePath($filePath);
        $file = $this->_storageDatabaseFactory->create()->loadByFilename($relativePath);
        if (!$file->getId()) {
            return false;
        }
        $stream = $this->_rootDir->openFile($filePath, 'w+');
        $stream->lock();
        $stream->write($filePath, $file->getContent());
        $stream->unlock();
        $stream->close();
        return true;
    }
}
