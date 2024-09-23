<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\CatalogUrlRewrite\Observer\ClearProductUrlsObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ClearProductUrlsObserverTest extends TestCase
{
    /**
     * @var ClearProductUrlsObserver
     */
    protected $clearProductUrlsObserver;

    /**
     * @var UrlPersistInterface|MockObject
     */
    protected $urlPersist;

    /**
     * @var Observer|MockObject
     */
    protected $observer;

    /**
     * @var Event|MockObject
     */
    protected $event;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * Test products returned by getBunch method of event object.
     *
     * @var array
     */
    protected $products = [
        [
            'sku' => 'sku',
            'url_key' => 'value1',
        ],
        [
            'sku' => 'sku3',
            'url_key' => 'value3',
        ],
        [
            'sku' => 'SKU5',
            'url_key' => 'value5',
        ]
    ];
    /**
     * @var SkuStorage|MockObject
     */
    private $skuStorage;

    /**
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    protected function setUp(): void
    {
        $this->skuStorage = $this->createMock(SkuStorage::class);
        $this->event = $this->getMockBuilder(Event::class)
            ->addMethods(['getBunch'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->event->expects($this->once())
            ->method('getBunch')
            ->willReturn($this->products);
        $this->observer = $this->getMockBuilder(Observer::class)
            ->onlyMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->observer->expects($this->exactly(1))
            ->method('getEvent')
            ->willReturn($this->event);
        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->clearProductUrlsObserver = new ClearProductUrlsObserver($this->urlPersist, $this->skuStorage);
    }

    /**
     * Test for clearProductUrls()
     */
    public function testClearProductUrls()
    {
        $oldSKus = [
            'sku' => ['entity_id' => 1],
            'sku5' => ['entity_id' => 5],
        ];

        $this->skuStorage->expects($this->any())
            ->method('has')
            ->willReturnCallback(function ($sku) use ($oldSKus) {
                return isset($oldSKus[strtolower($sku)]);
            });

        $this->skuStorage->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($sku) use ($oldSKus) {
                return $oldSKus[strtolower($sku)] ?? null;
            });

        $this->urlPersist->expects($this->once())
            ->method('deleteByData')
            ->with([
                'entity_id' => [1, 5],
                'entity_type' => 'product'
            ]);

        $this->clearProductUrlsObserver->execute($this->observer);
    }
}
