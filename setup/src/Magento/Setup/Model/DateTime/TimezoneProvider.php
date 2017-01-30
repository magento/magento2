<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Provider of Timezone instance
 */
class TimezoneProvider
{
    /**
     * Object Manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Instance of Timezone
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $timezone;

    /**
     * Init
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Get instance of Timezone
     *
     * @return \Magento\Framework\Stdlib\DateTime\Timezone
     */
    public function get()
    {
        if (!$this->timezone) {
            $this->timezone = $this->objectManagerProvider->get()->create(
                'Magento\Framework\Stdlib\DateTime\Timezone',
                ['scopeType' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT]
            );
        }
        return $this->timezone;
    }
}
