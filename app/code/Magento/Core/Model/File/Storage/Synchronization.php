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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File\Storage;

class Synchronization
{
    /**
     * Database storage factory
     *
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $_storageFactory;

    /**
     * File stream handler
     *
     * @var \Magento\Io\File
     */
    protected $_streamFactory;

    /**
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $storageFactory
     * @param \Magento\Filesystem\Stream\LocalFactory $streamFactory
     */
    public function __construct(
        \Magento\Core\Model\File\Storage\DatabaseFactory $storageFactory,
        \Magento\Filesystem\Stream\LocalFactory $streamFactory
    ) {
        $this->_storageFactory = $storageFactory;
        $this->_streamFactory = $streamFactory;
    }

    /**
     * Synchronize file
     *
     * @param string $relativeFileName
     * @param string $filePath
     * @throws \LogicException
     */
    public function synchronize($relativeFileName, $filePath)
    {
        /** @var $storage \Magento\Core\Model\File\Storage\Database */
        $storage = $this->_storageFactory->create();
        try {
            $storage->loadByFilename($relativeFileName);
        } catch (\Exception $e) {
        }
        if ($storage->getId()) {
            $directory = dirname($filePath);
            if (!is_dir($directory) && !mkdir($directory, 0777, true)) {
                throw new \LogicException('Could not create directory');
            }

            /** @var \Magento\Filesystem\StreamInterface $stream */
            $stream = $this->_streamFactory->create(array('path' => $filePath));
            try{
                $stream->open('w');
                $stream->lock(true);
                $stream->write($storage->getContent());
                $stream->unlock();
                $stream->close();
            } catch (\Magento\Filesystem\FilesystemException $e) {
                $stream->close();
            }
        }
    }
}
