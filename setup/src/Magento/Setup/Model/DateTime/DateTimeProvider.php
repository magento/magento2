<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\DateTime;

use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Provider of DateTime instance
 */
class DateTimeProvider
{
    /**
     * Timezone provider
     *
     * @var TimeZoneProvider
     */
    private $tzProvider;

    /**
     * Object Manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * DateTime instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * Init
     *
     * @param TimeZoneProvider $tzProvider
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(TimeZoneProvider $tzProvider, ObjectManagerProvider $objectManagerProvider)
    {
        $this->tzProvider = $tzProvider;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Get instance of DateTime
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function get()
    {
        if (!$this->dateTime) {
            $this->dateTime = $this->objectManagerProvider->get()->create(
                \Magento\Framework\Stdlib\DateTime\DateTime::class,
                ['localeDate' => $this->tzProvider->get()]
            );
        }
        return $this->dateTime;
    }
}
