<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogUrlRewrite\Observer\ClearProductUrlsObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ClearProductUrlsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClearProductUrlsObserver
     */
    protected $clearProductUrlsObserver;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersist;

    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importProduct;

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
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    protected function setUp()
    {
        $this->importProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->event = $this->getMockBuilder(Event::class)
            ->setMethods(['getBunch', 'getAdapter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->event->expects($this->once())
            ->method('getAdapter')
            ->willReturn($this->importProduct);
        $this->event->expects($this->once())
            ->method('getBunch')
            ->willReturn($this->products);
        $this->observer = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->observer->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->event);
        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->clearProductUrlsObserver = new ClearProductUrlsObserver($this->urlPersist);
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
        $this->importProduct->expects($this->once())
            ->method('getOldSku')
            ->willReturn($oldSKus);
        $this->urlPersist->expects($this->once())
            ->method('deleteByData')
            ->with([
                'entity_id' => [1, 5],
                'entity_type' => 'product'
            ]);

        $this->clearProductUrlsObserver->execute($this->observer);
    }
}
