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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Process file entity
 */
namespace Magento\Index\Model\Process;

class File extends \Magento\Io\File
{
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
     * Lock process file
     *
     * @param bool $nonBlocking
     * @return bool
     */
    public function processLock($nonBlocking = true)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        $this->_streamLocked = true;
        $lock = LOCK_EX;
        if ($nonBlocking) {
            $lock = $lock | LOCK_NB;
        }
        $result = flock($this->_streamHandler, $lock);
        // true if process is locked by other user
        $this->_processLocked = !$result;
        return $result;
    }

    /**
     * Unlock process file
     *
     * @return bool
     */
    public function processUnlock()
    {
        $this->_processLocked = null;
        return parent::streamUnlock();
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
            if (flock($this->_streamHandler, LOCK_EX | LOCK_NB)) {
                if ($needUnlock) {
                    flock($this->_streamHandler, LOCK_UN);
                    $this->_processLocked = false;
                } else {
                    $this->_streamLocked = true;
                }
                return false;
            }
            return true;
        }
    }
}
