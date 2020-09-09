<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Downloadable\Model\Source\TypeUpload;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Samples as SamplesData;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Samples;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Samples
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamplesTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var SamplesData|MockObject
     */
    protected $samplesDataMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var TypeUpload|MockObject
     */
    protected $typeUploadMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ArrayManager|MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var Samples
     */
    protected $samples;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $this->samplesDataMock = $this->createMock(SamplesData::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->typeUploadMock = $this->createMock(TypeUpload::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
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
        $this->urlBuilderMock->expects($this->never())
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
