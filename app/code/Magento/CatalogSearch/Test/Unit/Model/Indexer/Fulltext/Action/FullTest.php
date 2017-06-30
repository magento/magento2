<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Action;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FullTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $searchRequestConfig;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full */
    protected $object;

    protected function setUp()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchRequestConfig = $this->getMockBuilder(\Magento\Framework\Search\Request\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductStatus =
            $this->getMockBuilder(\Magento\Catalog\Model\Product\Attribute\Source\Status::class)
                ->disableOriginalConstructor()
                ->getMock();
        $engineProvider = $this->getMockBuilder(\Magento\CatalogSearch\Model\ResourceModel\EngineProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogSearchData = $this->getMockBuilder(\Magento\CatalogSearch\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fulltextResource = $this->getMockBuilder(\Magento\CatalogSearch\Model\ResourceModel\Fulltext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::class,
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
