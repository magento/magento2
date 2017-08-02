<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\System\Message\Media;

/**
 * @api
 * @since 2.0.0
 */
abstract class AbstractSynchronization implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Flag
     * @since 2.0.0
     */
    protected $_syncFlag;

    /**
     * Message identity
     *
     * @var string
     * @since 2.0.0
     */
    protected $_identity;

    /**
     * Is displayed flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isDisplayed = null;

    /**
     * @param \Magento\MediaStorage\Model\File\Storage\Flag $fileStorage
     * @since 2.0.0
     */
    public function __construct(\Magento\MediaStorage\Model\File\Storage\Flag $fileStorage)
    {
        $this->_syncFlag = $fileStorage->loadSelf();
    }

    /**
     * Check if message should be displayed
     *
     * @return bool
     * @since 2.0.0
     */
    abstract protected function _shouldBeDisplayed();

    /**
     * Retrieve unique message identity
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Check whether
     *
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;
    }
}
