<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Carrier;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Store\Model\Store;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractCarrierOnlineTest extends TestCase
{
    /**
     * Test identification number of product
     *
     * @var int
     */
    protected $productId = 1;

    /**
     * @var AbstractCarrierOnline|MockObject
     */
    protected $carrier;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    /**
     * @var MockObject
     */
    protected $stockItemData;

    protected function setUp(): void
    {
        $this->stockRegistry = $this->createMock(StockRegistry::class);
        $this->stockItemData = $this->createMock(Item::class);

        $this->stockRegistry->expects($this->any())->method('getStockItem')
            ->with($this->productId, 10)
            ->willReturn($this->stockItemData);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $carrierArgs = $objectManagerHelper->getConstructArguments(
            AbstractCarrierOnline::class,
            [
                'stockRegistry' => $this->stockRegistry,
                'xmlSecurity' => new Security(),
            ]
        );
        $this->carrier = $this->getMockBuilder(AbstractCarrierOnline::class)
            ->setConstructorArgs($carrierArgs)
            ->setMethods(['getConfigData', '_doShipmentRequest', 'collectRates'])
            ->getMock();
    }

    /**
     * @covers \Magento\Shipping\Model\Shipping::composePackagesForCarrier
     */
    public function testComposePackages()
    {
        $this->carrier->expects($this->any())->method('getConfigData')->willReturnCallback(function ($key) {
            $configData = [
                'max_package_weight' => 10,
                'showmethod'         => 1,
            ];
            return isset($configData[$key]) ? $configData[$key] : 0;
        });

        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getId')->willReturn($this->productId);

        $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getQty', 'getWeight', '__wakeup', 'getStore'])
            ->getMock();
        $item->expects($this->any())->method('getProduct')->willReturn($product);

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(10);
        $item->expects($this->any())->method('getStore')->willReturn($store);

        $request = new RateRequest();
        $request->setData('all_items', [$item]);
        $request->setData('dest_postcode', 1);

        /** Testable service calls to CatalogInventory module */
        $this->stockRegistry->expects($this->atLeastOnce())->method('getStockItem')->with($this->productId);
        $this->stockItemData->expects($this->atLeastOnce())->method('getEnableQtyIncrements')
            ->willReturn(true);
        $this->stockItemData->expects($this->atLeastOnce())->method('getQtyIncrements')
            ->willReturn(5);
        $this->stockItemData->expects($this->atLeastOnce())->method('getIsQtyDecimal')->willReturn(true);
        $this->stockItemData->expects($this->atLeastOnce())->method('getIsDecimalDivided')
            ->willReturn(true);

        $this->carrier->processAdditionalValidation($request);
    }

    public function testParseXml()
    {
        $xmlString = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><GetResponse><value>42</value>></GetResponse>";
        $simpleXmlElement = $this->carrier->parseXml($xmlString);
        $this->assertEquals('GetResponse', $simpleXmlElement->getName());
        $this->assertEquals(42, (int)$simpleXmlElement->value);
        $this->assertInstanceOf('SimpleXMLElement', $simpleXmlElement);
        $customSimpleXmlElement = $this->carrier->parseXml(
            $xmlString,
            Element::class
        );
        $this->assertInstanceOf(Element::class, $customSimpleXmlElement);
    }

    public function testParseXmlXXEXml()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The security validation of the XML document has failed.');
        $xmlString = '<!DOCTYPE scan [
            <!ENTITY test SYSTEM "php://filter/read=convert.base64-encode/resource='
            . __DIR__ . '/AbstractCarrierOnline/xxe-xml.txt">]><scan>&test;</scan>';

        $xmlElement = $this->carrier->parseXml($xmlString);

        // @codingStandardsIgnoreLine
        echo $xmlElement->asXML();
    }

    public function testParseXmlXQBXml()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The security validation of the XML document has failed.');
        $xmlString = '<?xml version="1.0"?>
            <!DOCTYPE test [
              <!ENTITY value "value">
              <!ENTITY value1 "&value;&value;&value;&value;&value;&value;&value;&value;&value;&value;">
              <!ENTITY value2 "&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;">
            ]>
            <test>&value2;</test>';

        $this->carrier->parseXml($xmlString);
    }
}
