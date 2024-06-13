<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Synchronize process status flag class
 */
namespace Magento\MediaStorage\Model\File\Storage;

/**
 * @api
 * @since 100.0.2
 */
class Flag extends \Magento\Framework\Flag
{
    /**
     * There was no synchronization
     */
    public const STATE_INACTIVE = 0;

    /**
     * Synchronize process is active
     */
    public const STATE_RUNNING = 1;

    /**
     * Synchronization finished
     */
    public const STATE_FINISHED = 2;

    /**
     * Synchronization finished and notify message was formed
     */
    public const STATE_NOTIFIED = 3;

    /**
     * Flag time to life in seconds
     */
    public const FLAG_TTL = 300;

    /**
     * Synchronize flag code
     *
     * @var string
     */
    protected $_flagCode = 'synchronize';

    /**
     * Pass error to flag
     *
     * @param \Exception $e
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passError(\Exception $e)
    {
        $data = $this->getFlagData();
        if (!is_array($data)) {
            $data = [];
        }
        $data['has_errors'] = true;
        $this->setFlagData($data);
        return $this;
    }
}
