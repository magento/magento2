<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Block\Adminhtml\Session;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\ConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Test class for \Magento\Security\Block\Adminhtml\Session\Activity testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActivityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Block\Adminhtml\Session\Activity
     */
    protected $block;

    /**
     * @var \Magento\Security\Model\AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     */
    protected $sessionsInfoCollection;

    /**
     * @var ConfigInterface
     */
    protected $securityConfig;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     */
    protected $collectionMock;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /*
     * @var RemoteAddress
     */
    protected $remoteAddressMock;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->sessionsInfoCollection =  $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->sessionsManager =  $this->getMock(
            '\Magento\Security\Model\AdminSessionsManager',
            ['getSessionsForCurrentUser'],
            [],
            '',
            false
        );

        $this->securityConfig =  $this->getMockBuilder(\Magento\Security\Model\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock =  $this->getMock(
            '\Magento\Security\Model\AdminSessionInfo',
            [],
            [],
            '',
            false
        );

        $this->localeDate = $this->getMockForAbstractClass(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            ['formatDateTime'],
            '',
            false
        );

        $this->collectionMock =  $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection',
            ['count', 'is_null'],
            [],
            '',
            false
        );

        $this->remoteAddressMock =  $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $this->objectManager->getObject(
            \Magento\Security\Block\Adminhtml\Session\Activity::class,
            [
                'sessionsManager' => $this->sessionsManager,
                'securityConfig' => $this->securityConfig,
                'localeDate' => $this->localeDate,
                'remoteAddress' => $this->remoteAddressMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testSessionInfoCollectionIsEmpty()
    {
        $this->sessionsManager->expects($this->once())
            ->method('getSessionsForCurrentUser')
            ->willReturn($this->collectionMock);
        $this->assertInstanceOf(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection',
            $this->block->getSessionInfoCollection()
        );
    }

    /**
     * @param bool $expectedResult
     * @param int $sessionsNumber
     * @dataProvider dataProviderAreMultipleSessionsActive
     */
    public function testAreMultipleSessionsActive($expectedResult, $sessionsNumber)
    {
        $this->sessionsManager->expects($this->once())
            ->method('getSessionsForCurrentUser')
            ->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->any())
            ->method('count')
            ->willReturn($sessionsNumber);
        $this->assertEquals($expectedResult, $this->block->areMultipleSessionsActive());
    }

    /**
     * @return array
     */
    public function dataProviderAreMultipleSessionsActive()
    {
        return [
            ['expectedResult' => false, 'sessionsNumber' => 0],
            ['expectedResult' => false, 'sessionsNumber' => 1],
            ['expectedResult' => true, 'sessionsNumber' => 2],
        ];
    }

    /**
     * @return void
     */
    public function testGetRemoteIp()
    {
        $this->remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->with(false);
        $this->block->getRemoteIp();
    }

    /**
     * @param string $timeString
     * @dataProvider dataProviderTime
     */
    public function testFormatDateTime($timeString)
    {
        $time = new \DateTime($timeString);
        $this->localeDate->expects($this->any())
            ->method('formatDateTime')
            ->with($time, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM)
            ->willReturn($time);
        $this->assertEquals($time, $this->block->formatDateTime($timeString));
    }

    /**
     * @return array
     */
    public function dataProviderTime()
    {
        return [
            ['timeString' => '2015-12-28 13:00:00'],
            ['timeString' => '2015-12-23 01:10:37']
        ];
    }
}
