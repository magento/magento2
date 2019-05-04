<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Links;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Links as LinksData;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Downloadable\Model\Source\TypeUpload;
use Magento\Downloadable\Model\Source\Shareable;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class LinksTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinksTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $locatorMock;

    /**
     * @var LinksData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linksDataMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var TypeUpload|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeUploadMock;

    /**
     * @var Shareable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shareableMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ArrayManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var Links
     */
    protected $links;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        $this->productMock = $this->createMock(ProductInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->linksDataMock = $this->createMock(LinksData::class);
        $this->typeUploadMock = $this->createMock(TypeUpload::class);
        $this->shareableMock = $this->createMock(Shareable::class);
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->links = $this->objectManagerHelper->getObject(
            Links::class,
            [
                'locator' => $this->locatorMock,
                'linksData' => $this->linksDataMock,
                'storeManager' => $this->storeManagerMock,
                'typeUpload' => $this->typeUploadMock,
                'shareable' => $this->shareableMock,
                'urlBuilder' => $this->urlBuilderMock,
                'arrayManager' => $this->arrayManagerMock,
            ]
        );
    }

    /**
     * @param bool $isPurchasedSeparatelyBool
     * @param string $isPurchasedSeparatelyStr
     * @return void
     * @dataProvider modifyDataDataProvider
     */
    public function testModifyData($isPurchasedSeparatelyBool, $isPurchasedSeparatelyStr)
    {
        $productId = 1;
        $linksTitle = 'Link Title';
        $linksData = 'Some data';
        $resultData = [
            $productId => [
                Links::DATA_SOURCE_DEFAULT => [
                    'links_title' => $linksTitle,
                    'links_purchased_separately' => $isPurchasedSeparatelyStr,
                ],
                'downloadable' => [
                    'link' => $linksData
                ]
            ]
        ];

        $this->locatorMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $this->linksDataMock->expects($this->once())
            ->method('getLinksTitle')
            ->willReturn($linksTitle);
        $this->linksDataMock->expects($this->once())
            ->method('isProductLinksCanBePurchasedSeparately')
            ->willReturn($isPurchasedSeparatelyBool);
        $this->linksDataMock->expects($this->once())
            ->method('getLinksData')
            ->willReturn($linksData);

        $this->assertEquals($resultData, $this->links->modifyData([]));
    }

    /**
     * @return array
     */
    public function modifyDataDataProvider()
    {
        return [
            ['isPurchasedSeparatelyBool' => true, 'PurchasedSeparatelyStr' => '1'],
            ['isPurchasedSeparatelyBool' => false, 'PurchasedSeparatelyStr' => '0'],
        ];
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $this->locatorMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())
            ->method('getTypeId');
        $this->storeManagerMock->expects($this->exactly(2))
            ->method('isSingleStoreMode');
        $this->typeUploadMock->expects($this->exactly(2))
            ->method('toOptionArray');
        $this->shareableMock->expects($this->once())
            ->method('toOptionArray');
        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('addSessionParam')
            ->willReturnSelf();
        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('getUrl');

        $currencyMock = $this->createMock(\Magento\Directory\Model\Currency::class);
        $currencyMock->expects($this->once())
            ->method('getCurrencySymbol');
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getBaseCurrency'])
            ->getMockForAbstractClass();
        $storeMock->expects($this->once())
            ->method('getBaseCurrency')
            ->willReturn($currencyMock);
        $this->locatorMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->arrayManagerMock->expects($this->exactly(9))
            ->method('set')
            ->willReturn([]);

        $this->assertEquals([], $this->links->modifyMeta([]));
    }
}
