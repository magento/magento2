<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Reader\Source\Dynamic;

use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\Config\Reader\Source\Dynamic\Store as StoreSource;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\Config\Reader\Source\Dynamic\Website as WebsiteSource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\DataObject;

/**
 * Class StoreTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopedFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionFactory;

    /**
     * @var Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $converter;

    /**
     * @var WebsiteFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteFactory;

    /**
     * @var Website|\PHPUnit\Framework\MockObject\MockObject
     */
    private $website;

    /**
     * @var WebsiteSource|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteSource;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $store;

    /**
     * @var StoreSource
     */
    private $storeSource;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->getMockBuilder(ScopedFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->converter = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteFactory = $this->getMockBuilder(\Magento\Store\Model\WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteSource = $this->getMockBuilder(WebsiteSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeSource = new StoreSource(
            $this->collectionFactory,
            $this->converter,
            $this->websiteFactory,
            $this->websiteSource,
            $this->storeManager
        );
    }

    public function testGet()
    {
        $scopeCode = 'myStore';
        $expectedResult = [
            'config/key1' => 'default_db_value1',
            'config/key3' => 'default_db_value3',
        ];
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with($scopeCode)
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->with(['scope' => ScopeInterface::SCOPE_STORES, 'scopeId' => 1])
            ->willReturn([
                new DataObject(['path' => 'config/key1', 'value' => 'default_db_value1']),
                new DataObject(['path' => 'config/key3', 'value' => 'default_db_value3']),
            ]);
        $this->websiteSource->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn([]);

        $this->converter->expects($this->at(0))
            ->method('convert')
            ->with([
                'config/key1' => 'default_db_value1',
                'config/key3' => 'default_db_value3'
            ])
            ->willReturnArgument(0);

        $this->converter->expects($this->at(1))
            ->method('convert')
            ->with($expectedResult)
            ->willReturnArgument(0);

        $this->assertEquals($expectedResult, $this->storeSource->get($scopeCode));
    }
}
