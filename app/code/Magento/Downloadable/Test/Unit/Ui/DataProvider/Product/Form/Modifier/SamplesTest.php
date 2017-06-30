<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Samples;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Samples as SamplesData;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Downloadable\Model\Source\TypeUpload;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class SamplesTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamplesTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var SamplesData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $samplesDataMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var TypeUpload|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeUploadMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ArrayManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var Samples
     */
    protected $samples;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->locatorMock = $this->getMock(LocatorInterface::class);
        $this->productMock = $this->getMock(ProductInterface::class);
        $this->samplesDataMock = $this->getMock(SamplesData::class, [], [], '', false);
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class);
        $this->typeUploadMock = $this->getMock(TypeUpload::class, [], [], '', false);
        $this->urlBuilderMock = $this->getMock(UrlInterface::class);
        $this->arrayManagerMock = $this->getMock(ArrayManager::class, [], [], '', false);
        $this->samples = $this->objectManagerHelper->getObject(
            Samples::class,
            [
                'locator' => $this->locatorMock,
                'samplesData' => $this->samplesDataMock,
                'storeManager' => $this->storeManagerMock,
                'arrayManager' => $this->arrayManagerMock,
                'typeUpload' => $this->typeUploadMock,
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testModifyData()
    {
        $productId = 1;
        $samplesTitle = 'Samples Title';
        $samplesData = 'Samples Data';
        $resultData = [
            $productId => [
                Samples::DATA_SOURCE_DEFAULT => [
                    'samples_title' => $samplesTitle,
                ],
                'downloadable' => [
                    'sample' => $samplesData,
                ],
            ]
        ];

        $this->locatorMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $this->samplesDataMock->expects($this->once())
            ->method('getSamplesTitle')
            ->willReturn($samplesTitle);
        $this->samplesDataMock->expects($this->once())
            ->method('getSamplesData')
            ->willReturn($samplesData);

        $this->assertEquals($resultData, $this->samples->modifyData([]));
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $this->locatorMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId');
        $this->storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode');
        $this->typeUploadMock->expects($this->once())
            ->method('toOptionArray');
        $this->urlBuilderMock->expects($this->once())
            ->method('addSessionParam')
            ->willReturnSelf();
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl');
        $this->arrayManagerMock->expects($this->exactly(6))
            ->method('set')
            ->willReturn([]);

        $this->assertEquals([], $this->samples->modifyMeta([]));
    }
}
