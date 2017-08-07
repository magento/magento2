<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\System\Message\Media;

/**
 * @api
 */
abstract class AbstractSynchronization implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Flag
     */
    protected $_syncFlag;

    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity;

    /**
     * Is displayed flag
     *
     * @var bool
     */
    protected $_isDisplayed = null;

    /**
     * @param \Magento\MediaStorage\Model\File\Storage\Flag $fileStorage
     */
    public function __construct(\Magento\MediaStorage\Model\File\Storage\Flag $fileStorage)
    {
        $this->_syncFlag = $fileStorage->loadSelf();
    }

    /**
     * Check if message should be displayed
     *
     * @return bool
     */
    abstract protected function _shouldBeDisplayed();

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if (null === $this->_isDisplayed) {
            $output = $this->_shouldBeDisplayed();
            if ($output) {
                $this->_syncFlag->setState(\Magento\MediaStorage\Model\File\Storage\Flag::STATE_NOTIFIED);
                $this->_syncFlag->save();
            }
            $this->_isDisplayed = $output;
        }
        return $this->_isDisplayed;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;
    }
}
