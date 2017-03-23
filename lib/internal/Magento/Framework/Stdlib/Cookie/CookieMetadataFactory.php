<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\ObjectManagerInterface;

/**
 * CookieMetadataFactory is used to construct SensitiveCookieMetadata and PublicCookieMetadata objects.
 * @api
 */
class CookieMetadataFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates a SensitiveCookieMetadata object with the supplied metadata.
     *
     * @param array $metadata
     * @return SensitiveCookieMetadata
     */
    public function createSensitiveCookieMetadata($metadata = [])
    {
        return $this->objectManager->create(
            \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata::class,
            ['metadata' => $metadata]
        );
    }

    /**
     * Creates a PublicCookieMetadata object with the supplied metadata.
     *
     * @param array $metadata
     * @return PublicCookieMetadata
     */
    public function createPublicCookieMetadata($metadata = [])
    {
        return $this->objectManager->create(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class,
            ['metadata' => $metadata]
        );
    }

    /**
     * Creates CookieMetadata object with the supplied metadata.
     *
     * @param array $metadata
     * @return CookieMetadata
     */
    public function createCookieMetadata($metadata = [])
    {
        return $this->objectManager->create(
            \Magento\Framework\Stdlib\Cookie\CookieMetadata::class,
            ['metadata' => $metadata]
        );
    }
}
