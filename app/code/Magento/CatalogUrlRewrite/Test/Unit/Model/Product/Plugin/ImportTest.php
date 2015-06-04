<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product\Plugin;

use Magento\ImportExport\Model\Import as ImportExport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ImportTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersist;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $import;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $object;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    public function setUp()
    {
        $this->object = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['getOldSku', '_populateToUrlGeneration'],
            [],
            '',
            false
        );
        $this->adapter->expects($this->any())->method('_populateToUrlGeneration')->willReturn($this->object);
        $this->adapter->expects($this->any())->method('getOldSku')->willReturn([
            'sku' => ['sku' => 'sku', 'url_key' => 'value1', 'entity_id' => '1'],
            'sku2' => ['sku' => 'sku2', 'url_key' => 'value2', 'entity_id' => '2']
        ]);
        $this->event = $this->getMock('\Magento\Framework\Event', ['getAdapter', 'getBunch'], [], '', false);
        $this->event->expects($this->any())->method('getAdapter')->willReturn($this->adapter);
        $this->event->expects($this->any())->method('getBunch')->willReturn([
            ['sku' => 'sku', 'url_key' => 'value1'], ['sku' => 'sku3', 'url_key' => 'value3']
        ]);
        $this->observer = $this->getMock('\Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->urlPersist = $this->getMockBuilder('\Magento\UrlRewrite\Model\UrlPersistInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlRewriteGenerator =
            $this->getMockBuilder('\Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator')
                ->disableOriginalConstructor()
                ->setMethods(['generate'])
                ->getMock();
        $this->productRepository = $this->getMockBuilder('\Magento\Catalog\Api\ProductRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->import = $this->objectManagerHelper->getObject(
            '\Magento\CatalogUrlRewrite\Model\Product\Plugin\Import',
            [
                'urlPersist' => $this->urlPersist,
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    /**
     * Test for afterImportData()
     */
    public function testAfterImportData()
    {
        $this->productUrlRewriteGenerator->expects($this->any())->method('generate')->willReturn(['url1', 'url2']);
        $this->import->afterImportData($this->observer);
    }

    /**
     * Test for clearProductUrls()
     */
    public function testClearProductUrls()
    {
        $this->import->clearProductUrls($this->observer);
    }
}
