<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Zend\Stdlib\Parameters;

/**
 * Test CookieScope
 *
 */
class CookieScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->get('Magento\Framework\App\RequestInterface');
    }

    public function testGetSensitiveCookieMetadataEmpty()
    {
        $serverVal = $_SERVER;
        $this->request->setServer(new Parameters(array_merge($_SERVER, ['HTTPS' => 'on'])));
        $cookieScope = $this->createCookieScope();

        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => true,
            ],
            $cookieScope->getSensitiveCookieMetadata()->__toArray());

        $this->request->setServer(new Parameters($serverVal));
    }

    public function testGetPublicCookieMetadataEmpty()
    {
        $cookieScope = $this->createCookieScope();

        $this->assertEmpty($cookieScope->getPublicCookieMetadata()->__toArray());
    }

    public function testGetSensitiveCookieMetadataDefaults()
    {
        $defaultValues = [
            SensitiveCookieMetadata::KEY_PATH => 'default path',
            SensitiveCookieMetadata::KEY_DOMAIN => 'default domain',
        ];
        $sensitive = $this->createSensitiveMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'sensitiveCookieMetadata' => $sensitive,
                'publicCookieMetadata' => null,
                'cookieMetadata' => null,
            ]
        );
        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_PATH => 'default path',
                SensitiveCookieMetadata::KEY_DOMAIN => 'default domain',
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => false,
            ],
            $cookieScope->getSensitiveCookieMetadata()->__toArray()
        );
    }

    public function testGetPublicCookieMetadataDefaults()
    {
        $defaultValues = [
            PublicCookieMetadata::KEY_PATH => 'default path',
            PublicCookieMetadata::KEY_DOMAIN => 'default domain',
            PublicCookieMetadata::KEY_DURATION => 'default duration',
            PublicCookieMetadata::KEY_HTTP_ONLY => 'default http',
            PublicCookieMetadata::KEY_SECURE => 'default secure',
        ];
        $public = $this->createPublicMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'publicCookieMetadata' => $public,
            ]
        );

        $this->assertEquals($defaultValues, $cookieScope->getPublicCookieMetadata()->__toArray());
    }

    public function testGetCookieMetadataDefaults()
    {
        $defaultValues = [
            CookieMetadata::KEY_PATH => 'default path',
            CookieMetadata::KEY_DOMAIN => 'default domain',
        ];
        $cookieMetadata = $this->createCookieMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'deleteCookieMetadata' => $cookieMetadata,
            ]
        );
        $this->assertEquals($defaultValues, $cookieScope->getCookieMetadata()->__toArray());
    }

    public function testGetSensitiveCookieMetadataOverrides()
    {
        $defaultValues = [
            SensitiveCookieMetadata::KEY_PATH => 'default path',
            SensitiveCookieMetadata::KEY_DOMAIN => 'default domain',
        ];
        $overrideValues = [
            SensitiveCookieMetadata::KEY_PATH => 'override path',
            SensitiveCookieMetadata::KEY_DOMAIN => 'override domain',
        ];
        $sensitive = $this->createSensitiveMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'sensitiveCookieMetadata' => $sensitive,
            ]
        );
        $override = $this->createSensitiveMetadata($overrideValues);
        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_PATH => 'override path',
                SensitiveCookieMetadata::KEY_DOMAIN => 'override domain',
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => false,
            ],
            $cookieScope->getSensitiveCookieMetadata($override)->__toArray());
    }

    public function testGetPublicCookieMetadataOverrides()
    {
        $defaultValues = [
            PublicCookieMetadata::KEY_PATH => 'default path',
            PublicCookieMetadata::KEY_DOMAIN => 'default domain',
            PublicCookieMetadata::KEY_DURATION => 'default duration',
            PublicCookieMetadata::KEY_HTTP_ONLY => 'default http',
            PublicCookieMetadata::KEY_SECURE => 'default secure',
        ];
        $overrideValues = [
            PublicCookieMetadata::KEY_PATH => 'override path',
            PublicCookieMetadata::KEY_DOMAIN => 'override domain',
            PublicCookieMetadata::KEY_DURATION => 'override duration',
            PublicCookieMetadata::KEY_HTTP_ONLY => 'override http',
            PublicCookieMetadata::KEY_SECURE => 'override secure',
        ];
        $public = $this->createPublicMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'publicCookieMetadata' => $public,
            ]
        );
        $override = $this->createPublicMetadata($overrideValues);

        $this->assertEquals($overrideValues, $cookieScope->getPublicCookieMetadata($override)->__toArray());
    }

    public function testGetCookieMetadataOverrides()
    {
        $defaultValues = [
            CookieMetadata::KEY_PATH => 'default path',
            CookieMetadata::KEY_DOMAIN => 'default domain',
        ];
        $overrideValues = [
            CookieMetadata::KEY_PATH => 'override path',
            CookieMetadata::KEY_DOMAIN => 'override domain',
        ];
        $deleteCookieMetadata = $this->createCookieMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'deleteCookieMetadata' => $deleteCookieMetadata,
            ]
        );
        $override = $this->createCookieMetadata($overrideValues);
        $this->assertEquals($overrideValues, $cookieScope->getCookieMetadata($override)->__toArray());
    }

    /**
     * Creates a CookieScope object with the given parameters.
     *
     * @param array $params
     * @return CookieScope
     */
    protected function createCookieScope($params = [])
    {
        return $this->objectManager->create('Magento\Framework\Stdlib\Cookie\CookieScope', $params);
    }

    /**
     * Creates a SensitiveCookieMetadata object with provided metadata values.
     *
     * @param array $metadata
     * @return SensitiveCookieMetadata
     */
    protected function createSensitiveMetadata($metadata = [])
    {
        return $this->objectManager->create(
            'Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata',
            ['metadata' => $metadata]
        );
    }

    /**
     * Creates a PublicCookieMetadata object with provided metadata values.
     *
     * @param array $metadata
     * @return PublicCookieMetadata
     */
    protected function createPublicMetadata($metadata = [])
    {
        return $this->objectManager->create(
            'Magento\Framework\Stdlib\Cookie\PublicCookieMetadata',
            ['metadata' => $metadata]
        );
    }

    /**
     * Creates a CookieMetadata object with provided metadata values.
     *
     * @param array $metadata
     * @return CookieMetadata
     */
    protected function createCookieMetadata($metadata = [])
    {
        return $this->objectManager->create(
            'Magento\Framework\Stdlib\Cookie\CookieMetadata',
            ['metadata' => $metadata]
        );
    }
}
