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
namespace Magento\Index\Model\Process;

use Magento\Filesystem\FilesystemException;
use Magento\Filesystem\File\WriteInterface;

/**
 * Process file entity
 */
class File
{
    /**
     * Stream handle instance
     *
     * @var WriteInterface
     */
    protected $_streamHandler;

    /**
     * Is stream locked
     *
     * @var bool
     */
    protected $_streamLocked;

    /**
     * Process lock flag:
     * - true  - if process is already locked by another user
     * - false - if process is locked by us
     * - null  - unknown lock status
     *
     * @var bool
     */
    protected $_processLocked = null;

    /**
     * @param WriteInterface $streamHandler
     */
    public function __construct(WriteInterface $streamHandler)
    {
        $this->_streamHandler = $streamHandler;
    }

    /**
     * Lock process file
     *
     * @param bool $nonBlocking
     * @return void
     */
    public function processLock($nonBlocking = true)
    {
        $lockMode = LOCK_EX;
        if ($nonBlocking) {
            $lockMode = $lockMode | LOCK_NB;
        }
        try {
            $this->_streamHandler->lock($lockMode);
            $this->_streamLocked = true;
        } catch (FilesystemException $e) {
            $this->_streamLocked = false;
        }
        // true if process is locked by other user
        $this->_processLocked = !$this->_streamLocked;
    }

    /**
     * Unlock process file
     *
     * @return bool
     */
    public function processUnlock()
    {
        $this->_processLocked = null;
        try {
            $this->_streamHandler->unlock();
            $this->_streamLocked = false;
        } catch (FilesystemException $e) {
            $this->_streamLocked = true;
        }
        return !$this->_streamLocked;
    }

    /**
     * Check if process is locked by another user
     *
     * @param bool $needUnlock
     * @return bool|null
     */
    public function isProcessLocked($needUnlock = true)
    {
        if (!$this->_streamHandler) {
            return null;
        }

        if ($this->_processLocked !== null) {
            return $this->_processLocked;
        } else {
            try {
                $this->_streamHandler->lock(LOCK_EX | LOCK_NB);
                if ($needUnlock) {
                    $this->_streamHandler->unlock();
                    $this->_streamLocked = false;
                } else {
                    $this->_streamLocked = true;
                }
                return false;
            } catch (FilesystemException $e) {
                return true;
            }
        }
    }
}
