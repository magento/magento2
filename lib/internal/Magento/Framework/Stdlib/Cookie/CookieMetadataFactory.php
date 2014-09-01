<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\ObjectManager;

/**
 * CookieMetadataFactory is used to construct SensitiveCookieMetadata and PublicCookieMetadata objects.
 */
class CookieMetadataFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
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
