<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Provider of Timezone instance
 * @since 2.1.0
 */
class TimeZoneProvider
{
    /**
     * Object Manager provider
     *
     * @var ObjectManagerProvider
     * @since 2.1.0
     */
    private $objectManagerProvider;

    /**
     * Instance of Timezone
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     * @since 2.1.0
     */
    private $timezone;

    /**
     * Init
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @since 2.1.0
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Get instance of Timezone
     *
     * @return \Magento\Framework\Stdlib\DateTime\Timezone
     * @since 2.1.0
     */
    public function get()
    {
        if (!$this->timezone) {
            $this->timezone = $this->objectManagerProvider->get()->create(
                \Magento\Framework\Stdlib\DateTime\Timezone::class,
                ['scopeType' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT]
            );
        }
        return $this->timezone;
    }
}
