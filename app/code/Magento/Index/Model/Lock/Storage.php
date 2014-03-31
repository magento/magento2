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
 * @category    Magento
 * @package     Magento_Index
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Lock file storage for index processes
 */
namespace Magento\Index\Model\Lock;

use Magento\Index\Model\Process\File;
use Magento\Index\Model\Process\FileFactory;
use Magento\App\Filesystem;
use Magento\Filesystem\Directory\WriteInterface;

class Storage
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * File handlers by process IDs
     *
     * @var array
     */
    protected $_fileHandlers = array();

    /**
     * Directory instance
     *
     * @var WriteInterface
     */
    protected $_varDirectory;

    /**
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     */
    public function __construct(FileFactory $fileFactory, Filesystem $filesystem)
    {
        $this->_fileFactory = $fileFactory;
        $this->_varDirectory = $filesystem->getDirectoryWrite(Filesystem::VAR_DIR);
    }

    /**
     * Get file handler by process ID
     *
     * @param string $processId
     * @return File
     */
    public function getFile($processId)
    {
        if (!isset($this->_fileHandlers[$processId])) {
            $this->_varDirectory->create('locks');
            $fileName = 'locks/index_process_' . $processId . '.lock';
            $stream = $this->_varDirectory->openFile($fileName, 'w+');
            $stream->write(date('r'));
            $this->_fileHandlers[$processId] = $this->_fileFactory->create(array('streamHandler' => $stream));
        }
        return $this->_fileHandlers[$processId];
    }
}
