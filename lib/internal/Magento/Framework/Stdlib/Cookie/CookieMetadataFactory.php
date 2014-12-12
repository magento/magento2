<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\ObjectManagerInterface;

/**
 * CookieMetadataFactory is used to construct SensitiveCookieMetadata and PublicCookieMetadata objects.
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
            'Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata',
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
            'Magento\Framework\Stdlib\Cookie\PublicCookieMetadata',
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
            'Magento\Framework\Stdlib\Cookie\CookieMetadata',
            ['metadata' => $metadata]
        );
    }
}
