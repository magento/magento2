<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Block\Adminhtml\Session;

use Magento\Backend\Helper\Data as DirectoryHelper;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Block\Adminhtml\Session\Activity;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Block\Adminhtml\Session\Activity testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActivityTest extends TestCase
{
    /**
     * @var  Activity
     */
    protected $block;

    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var ConfigInterface
     */
    protected $securityConfig;

    /**
     * @var Collection
     */
    protected $collectionMock;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddressMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        
        $jsonHelperMock = $this->createMock(JsonHelper::class);
        $directoryHelperMock = $this->createMock(DirectoryHelper::class);
        
        $this->objectManager->prepareObjectManager([
            [JsonHelper::class, $jsonHelperMock],
            [DirectoryHelper::class, $directoryHelperMock]
        ]);
        
        $this->localeDate = $this->createMock(TimezoneInterface::class);

        $this->sessionsManager = $this->createPartialMock(
            AdminSessionsManager::class,
            ['getSessionsForCurrentUser']
        );

        $this->securityConfig = $this->createMock(ConfigInterface::class);

        $this->collectionMock = $this->createPartialMock(Collection::class, ['count']);

        $this->remoteAddressMock = $this->createMock(RemoteAddress::class);

        $this->block = $this->objectManager->getObject(
            Activity::class,
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
            Collection::class,
            $this->block->getSessionInfoCollection()
        );
    }

    /**
     * @param bool $expectedResult
     * @param int $sessionsNumber
     */
    #[DataProvider('dataProviderAreMultipleSessionsActive')]
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
    public static function dataProviderAreMultipleSessionsActive()
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
     */
    #[DataProvider('dataProviderTime')]
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
    public static function dataProviderTime()
    {
        return [
            ['timeString' => '2015-12-28 13:00:00'],
            ['timeString' => '2015-12-23 01:10:37']
        ];
    }
}
