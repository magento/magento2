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

class Config
{
    /**
     * Config cache file path
     *
     * @var string
     */
    protected $_cacheFile;

    /**
     * Loaded config
     *
     * @var array
     */
    protected $_config;

    /**
     * File stream handler
     *
     * @var \Magento\Io\File
     */
    protected $_streamFactory;

    /**
     * @param \Magento\Core\Model\File\Storage $storage
     * @param \Magento\Filesystem\Stream\LocalFactory $streamFactory
     * @param string $cacheFile
     */
    public function __construct(
        \Magento\Core\Model\File\Storage $storage, \Magento\Filesystem\Stream\LocalFactory $streamFactory, $cacheFile
    ) {
        $this->_config = $storage->getScriptConfig();
        $this->_streamFactory = $streamFactory;
        $this->_cacheFile = $cacheFile;
    }

    /**
     * Retrieve media directory
     *
     * @return string
     */
    public function getMediaDirectory()
    {
        return $this->_config['media_directory'];
    }

    /**
     * Retrieve list of allowed resources
     *
     * @return array
     */
    public function getAllowedResources()
    {
        return $this->_config['allowed_resources'];
    }

    /**
     * Save config in cache file
     */
    public function save()
    {
        /** @var \Magento\Filesystem\StreamInterface $stream */
        $stream = $this->_streamFactory->create(array('path' => $this->_cacheFile));
        try{
            $stream->open('w');
            $stream->lock(true);
            $stream->write(json_encode($this->_config));
            $stream->unlock();
            $stream->close();
        } catch (\Magento\Filesystem\FilesystemException $e) {
            $stream->close();
        }
    }
}
