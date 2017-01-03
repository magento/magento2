<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\FlagFactory;
use \Magento\Framework\Flag\FlagResource;

/**
 * Class NotificationTime
 *
 */
class NotificationTime
{
    const NOTIFICATION_TIME = 'notification_time';
    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * NotificationTime constructor.
     *
     * @param FlagFactory $flagFactory
     * @param FlagResource $flagResource
     */
    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource
    ) {
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
    }

    /**
     * Stores last notification time
     *
     * @param string $value
     * @return bool
     */
    public function storeLastTimeNotification($value)
    {
        $flag = $this->flagFactory->create(
            [
                'data' => [
                    'flag_code' => self::NOTIFICATION_TIME
                ]
            ]
        );
        $flag->setFlagData($value);
        $this->flagResource->save($flag);
        return true;
    }

    /**
     * Returns last time when merchant was notified about Analytic services
     *
     * @return int
     */
    public function getLastTimeNotification()
    {
        /** @var \Magento\Framework\Flag $flag */
        $flag = $this->flagResource->load($this->flagFactory->create(), self::NOTIFICATION_TIME);
        return $flag->getFlagData();
    }
}
