<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\ImportExport\Model\Import as ImportExport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Store\Model\Store;
use Magento\Framework\App\ResourceConnection;

/**
 * Class ImportTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ClearProductUrlsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogUrlRewrite\Observer\ClearProductUrlsObserver
     */
    protected $clearProductUrlsObserver;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersist;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
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
            ImportProduct::COL_STORE => Store::DEFAULT_STORE_ID,
        ],
        [
            'sku' => 'sku3',
            'url_key' => 'value3',
            ImportProduct::COL_STORE => 'not global',
        ],
    ];

    /**
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    protected function setUp()
    {
        $this->importProduct = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product',
            [
                'getNewSku',
                'getProductCategories',
                'getProductWebsites',
                'getStoreIdByCode',
                'getCategoryProcessor',
            ],
            [],
            '',
            false
        );

        $this->event = $this->getMock('\Magento\Framework\Event', ['getAdapter', 'getBunch'], [], '', false);
        $this->event->expects($this->any())->method('getAdapter')->willReturn($this->importProduct);
        $this->event->expects($this->any())->method('getBunch')->willReturn($this->products);
        $this->observer = $this->getMock('\Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->urlPersist = $this->getMockBuilder('\Magento\UrlRewrite\Model\UrlPersistInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->clearProductUrlsObserver = $this->objectManagerHelper->getObject(
            '\Magento\CatalogUrlRewrite\Observer\ClearProductUrlsObserver',
            [
                'urlPersist' => $this->urlPersist,
            ]
        );
    }

    /**
     * Test for clearProductUrls()
     */
    public function testClearProductUrls()
    {
        $this->clearProductUrlsObserver->execute($this->observer);
    }
}
