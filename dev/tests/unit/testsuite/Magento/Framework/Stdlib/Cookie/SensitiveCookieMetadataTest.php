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

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Test SensitiveCookieMetaData
 *
 */
class SensitiveCookieMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
    private $objectManager;

    /** @var SensitiveCookieMetadata */
    private $sensitiveCookieMetadata;

    /** @var  \Magento\Framework\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $storeManagerMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sensitiveCookieMetadata = $this->objectManager->getObject(
            'Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata',
            [
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * @param array $metadata
     * @param bool $httpOnly
     * @dataProvider constructorAndGetHttpOnlyTestDataProvider
     */
    public function testConstructorAndGetHttpOnly($metadata, $httpOnly)
    {
        /** @var \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata $object */
        $object = $this->objectManager->getObject(
            'Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata',
            [
                'storeManager' => $this->storeManagerMock,
                'metadata' => $metadata,

            ]
        );
        $this->assertEquals($httpOnly, $object->getHttpOnly());
        $this->assertEquals('domain', $object->getDomain());
        $this->assertEquals('path', $object->getPath());
    }

    public function constructorAndGetHttpOnlyTestDataProvider()
    {
        return [
            'with httpOnly' => [
                [
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                false,
            ],
            'without httpOnly' => [
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                true,
            ],
        ];
    }

    /**
     * @param bool $isCurrentlySecure
     * @param array $metadata
     * @param bool $expected
     * @param int $callNum
     * @dataProvider getSecureDataProvider
     */
    public function testGetSecure($isCurrentlySecure, $metadata, $expected, $callNum = 1)
    {
        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->exactly($callNum))
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->exactly($callNum))
            ->method('isCurrentlySecure')
            ->willReturn($isCurrentlySecure);

        /** @var \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata $object */
        $object = $this->objectManager->getObject(
            'Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata',
            [
                'storeManager' => $this->storeManagerMock,
                'metadata' => $metadata,
            ]
        );
        $this->assertEquals($expected, $object->getSecure());
    }

    public function getSecureDataProvider()
    {
        return [
            'with secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                false,
                0,
            ],
            'without secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                true,
            ],
            'without secure 2' => [
                false,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                false,
            ],
        ];
    }

    /**
     * @param bool $isCurrentlySecure
     * @param array $metadata
     * @param bool $expected
     * @param int $callNum
     * @dataProvider toArrayDataProvider
     */
    public function testToArray($isCurrentlySecure, $metadata, $expected, $callNum = 1)
    {
        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->exactly($callNum))
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->exactly($callNum))
            ->method('isCurrentlySecure')
            ->willReturn($isCurrentlySecure);

        /** @var \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata $object */
        $object = $this->objectManager->getObject(
            'Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata',
            [
                'storeManager' => $this->storeManagerMock,
                'metadata' => $metadata,
            ]
        );
        $this->assertEquals($expected, $object->__toArray());
    }

    public function toArrayDataProvider()
    {
        return [
            'with secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => 1,
                ],
                0,
            ],
            'without secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                [
                    SensitiveCookieMetadata::KEY_SECURE => true,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => 1,
                ],
            ],
            'without secure 2' => [
                false,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => 1,
                ],
            ],
        ];
    }

    /**
     * @param String $setMethodName
     * @param String $getMethodName
     * @param String $expectedValue
     * @dataProvider getMethodData
     */

    public function testGetters($setMethodName, $getMethodName, $expectedValue)
    {
        $this->sensitiveCookieMetadata->$setMethodName($expectedValue);
        $this->assertSame($expectedValue, $this->sensitiveCookieMetadata->$getMethodName());
    }

    /**
     * @return array
     */
    public function getMethodData()
    {
        return [
            "getDomain" => ["setDomain", 'getDomain', "example.com"],
            "getPath" => ["setPath", 'getPath', "path"]
        ];
    }
}
