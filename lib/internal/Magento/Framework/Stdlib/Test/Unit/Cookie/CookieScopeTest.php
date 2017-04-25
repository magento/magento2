<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Test\Unit\Cookie;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieScope;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;

/**
 * Test CookieScope
 *
 * @coversDefaultClass Magento\Framework\Stdlib\Cookie\CookieScope
 */
class CookieScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    private $defaultScopeParams;

    private $requestMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();
        $this->requestMock->expects($this->any())
            ->method('isSecure')->willReturn(true);
        $this->objectManager = new ObjectManager($this);
        $cookieMetadataFactory = $this
            ->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class)
            ->disableOriginalConstructor()->getMock();
        $cookieMetadataFactory->expects($this->any())
            ->method('createSensitiveCookieMetadata')
            ->will($this->returnCallback([$this, 'createSensitiveMetadata']));
        $cookieMetadataFactory->expects($this->any())
            ->method('createPublicCookieMetadata')
            ->will($this->returnCallback([$this, 'createPublicMetadata']));
        $cookieMetadataFactory->expects($this->any())
            ->method('createCookieMetadata')
            ->will($this->returnCallback([$this, 'createCookieMetadata']));
        $this->defaultScopeParams = [
            'cookieMetadataFactory' => $cookieMetadataFactory,
        ];
    }

    /**
     * @covers ::getSensitiveCookieMetadata
     */
    public function testGetSensitiveCookieMetadataEmpty()
    {
        $cookieScope = $this->createCookieScope();

        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => true,
            ],
            $cookieScope->getSensitiveCookieMetadata()->__toArray()
        );
    }

    /**
     * @covers ::getPublicCookieMetadata
     */
    public function testGetPublicCookieMetadataEmpty()
    {
        $cookieScope = $this->createCookieScope();

        $this->assertEmpty($cookieScope->getPublicCookieMetadata()->__toArray());
    }

    /**
     * @covers ::getCookieMetadata
     */
    public function testGetCookieMetadataEmpty()
    {
        $cookieScope = $this->createCookieScope();

        $this->assertEmpty($cookieScope->getPublicCookieMetadata()->__toArray());
    }

    /**
     * @covers ::createSensitiveMetadata ::getPublicCookieMetadata
     */
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
                'deleteCookieMetadata' => null,
            ]
        );

        $this->assertEmpty($cookieScope->getPublicCookieMetadata()->__toArray());
        $this->assertEmpty($cookieScope->getCookieMetadata()->__toArray());
        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_PATH => 'default path',
                SensitiveCookieMetadata::KEY_DOMAIN => 'default domain',
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => true,
            ],
            $cookieScope->getSensitiveCookieMetadata()->__toArray()
        );
    }

    /**
     * @covers ::createSensitiveMetadata ::getPublicCookieMetadata ::getCookieMetadata
     */
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
                'sensitiveCookieMetadata' => null,
                'publicCookieMetadata' => $public,
                'deleteCookieMetadata' => null,
            ]
        );

        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => true,
            ],
            $cookieScope->getSensitiveCookieMetadata()->__toArray()
        );
        $this->assertEmpty($cookieScope->getCookieMetadata()->__toArray());
        $this->assertEquals($defaultValues, $cookieScope->getPublicCookieMetadata()->__toArray());
    }

    /**
     * @covers ::createSensitiveMetadata ::getPublicCookieMetadata ::getCookieMetadata
     */
    public function testGetCookieMetadataDefaults()
    {
        $defaultValues = [
            CookieMetadata::KEY_PATH => 'default path',
            CookieMetadata::KEY_DOMAIN => 'default domain',
        ];
        $cookieMetadata = $this->createCookieMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'sensitiveCookieMetadata' => null,
                'publicCookieMetadata' => null,
                'deleteCookieMetadata' => $cookieMetadata,
            ]
        );

        $this->assertEquals($defaultValues, $cookieScope->getCookieMetadata()->__toArray());
    }

    /**
     * @covers ::createSensitiveMetadata ::getPublicCookieMetadata ::getCookieMetadata
     */
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
                'publicCookieMetadata' => null,
                'deleteCookieMetadata' => null,
            ]
        );
        $override = $this->createSensitiveMetadata($overrideValues);

        $this->assertEmpty($cookieScope->getPublicCookieMetadata($this->createPublicMetadata())->__toArray());
        $this->assertEmpty($cookieScope->getCookieMetadata($this->createCookieMetadata())->__toArray());
        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_PATH => 'override path',
                SensitiveCookieMetadata::KEY_DOMAIN => 'override domain',
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => true,
            ],
            $cookieScope->getSensitiveCookieMetadata($override)->__toArray()
        );
    }

    /**
     * @covers ::createSensitiveMetadata ::getPublicCookieMetadata ::getCookieMetadata
     */
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
                'sensitiveCookieMetadata' => null,
                'publicCookieMetadata' => $public,
                'cookieMetadata' => null,
            ]
        );
        $override = $this->createPublicMetadata($overrideValues);
        $this->assertEquals($overrideValues, $cookieScope->getPublicCookieMetadata($override)->__toArray());
    }

    /**
     * @covers ::createSensitiveMetadata ::getPublicCookieMetadata ::getCookieMetadata
     */
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
        $cookieMeta = $this->createCookieMetadata($defaultValues);
        $cookieScope = $this->createCookieScope(
            [
                'sensitiveCookieMetadata' => null,
                'publicCookieMetadata' => null,
                'deleteCookieMetadata' => $cookieMeta,
            ]
        );
        $override = $this->createCookieMetadata($overrideValues);

        $this->assertEquals(
            [
                SensitiveCookieMetadata::KEY_HTTP_ONLY => true,
                SensitiveCookieMetadata::KEY_SECURE => true,
            ],
            $cookieScope->getSensitiveCookieMetadata($this->createSensitiveMetadata())->__toArray()
        );
        $this->assertEquals(
            [],
            $cookieScope->getPublicCookieMetadata($this->createPublicMetadata())->__toArray()
        );
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
        $params = array_merge($this->defaultScopeParams, $params);
        return $this->objectManager->getObject(\Magento\Framework\Stdlib\Cookie\CookieScope::class, $params);
    }

    /**
     * Creates a SensitiveCookieMetadata object with provided metadata values.
     *
     * @param array $metadata
     * @return SensitiveCookieMetadata
     */
    public function createSensitiveMetadata($metadata = [])
    {
        return $this->objectManager->getObject(
            \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata::class,
            ['metadata' => $metadata, 'request' => $this->requestMock]
        );
    }

    /**
     * Creates a PublicCookieMetadata object with provided metadata values.
     *
     * @param array $metadata
     * @return PublicCookieMetadata
     */
    public function createPublicMetadata($metadata = [])
    {
        return $this->objectManager->getObject(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class,
            ['metadata' => $metadata]
        );
    }

    /**
     * Creates a CookieMetadata object with provided metadata values.
     *
     * @param array $metadata
     * @return CookieMetadata
     */
    public function createCookieMetadata($metadata = [])
    {
        return $this->objectManager->getObject(
            \Magento\Framework\Stdlib\Cookie\CookieMetadata::class,
            ['metadata' => $metadata]
        );
    }
}
