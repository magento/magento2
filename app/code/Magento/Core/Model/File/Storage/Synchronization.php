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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File\Storage;

use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Filesystem\FilesystemException;

/**
 * Class Synchronization
 */
class Synchronization
{
    /**
     * Database storage factory
     *
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $storageFactory;

    /**
     * File stream handler
     *
     * @var DirectoryWrite
     */
    protected $pubDirectory;

    /**
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $storageFactory
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Core\Model\File\Storage\DatabaseFactory $storageFactory,
        \Magento\Framework\App\Filesystem $filesystem
    ) {
        $this->storageFactory = $storageFactory;
        $this->pubDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::PUB_DIR);
    }

    /**
     * Synchronize file
     *
     * @param string $relativeFileName
     * @param string $filePath
     * @return void
     * @throws \LogicException
     */
    public function synchronize($relativeFileName, $filePath)
    {
        /** @var $storage \Magento\Core\Model\File\Storage\Database */
        $storage = $this->storageFactory->create();
        try {
            $storage->loadByFilename($relativeFileName);
        } catch (\Exception $e) {
        }
        if ($storage->getId()) {
            /** @var Write $file */
            $file = $this->pubDirectory->openFile($this->pubDirectory->getRelativePath($filePath), 'w');
            try {
                $file->lock();
                $file->write($storage->getContent());
                $file->unlock();
                $file->close();
            } catch (FilesystemException $e) {
                $file->close();
            }
        }
    }
}
