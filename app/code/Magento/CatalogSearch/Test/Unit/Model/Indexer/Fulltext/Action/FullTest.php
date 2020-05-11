<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogSearch\Helper\Data;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FullTest extends TestCase
{
    /** @var Config|MockObject */
    protected $searchRequestConfig;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var Full */
    protected $object;

    protected function setUp(): void
    {
        $this->markTestSkipped("MC-18332: Mysql Search Engine is deprecated and will be removed");
        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchRequestConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductStatus =
            $this->getMockBuilder(Status::class)
                ->disableOriginalConstructor()
                ->getMock();
        $engineProvider = $this->getMockBuilder(EngineProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $catalogSearchData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeResolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $localeDate = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $fulltextResource = $this->getMockBuilder(Fulltext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->object = $objectManagerHelper->getObject(
            Full::class,
            [
                'resource' => $resource,
                'catalogProductType' => $catalogProductType,
                'eavConfig' => $eavConfig,
                'searchRequestConfig' => $this->searchRequestConfig,
                'catalogProductStatus' => $catalogProductStatus,
                'engineProvider' => $engineProvider,
                'eventManager' => $eventManager,
                'catalogSearchData' => $catalogSearchData,
                'scopeConfig' => $scopeConfig,
                'storeManager' => $this->storeManager,
                'dateTime' => $dateTime,
                'localeResolver' => $localeResolver,
                'localeDate' => $localeDate,
                'fulltextResource' => $fulltextResource
            ]
        );
    }

    public function testReindexAll()
    {
        $this->storeManager->expects($this->once())->method('getStores')->willReturn([]);
        $this->searchRequestConfig->expects($this->once())->method('reset');
        $this->object->reindexAll();
    }
}
