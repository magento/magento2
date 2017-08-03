<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\DateTime;

use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Provider of DateTime instance
 * @since 2.1.0
 */
class DateTimeProvider
{
    /**
     * Timezone provider
     *
     * @var TimeZoneProvider
     * @since 2.1.0
     */
    private $tzProvider;

    /**
     * Object Manager provider
     *
     * @var ObjectManagerProvider
     * @since 2.1.0
     */
    private $objectManagerProvider;

    /**
     * DateTime instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.1.0
     */
    private $dateTime;

    /**
     * Init
     *
     * @param TimeZoneProvider $tzProvider
     * @param ObjectManagerProvider $objectManagerProvider
     * @since 2.1.0
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
     * @since 2.1.0
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
