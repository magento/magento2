<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Samples;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * Test class to cover Sample Modifier
 *
 * Class \Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data\SampleTest
 */
class SamplesTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    private $locatorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var DownloadableFile|MockObject
     */
    private $downloadableFileMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    /**
     * @var Samples
     */
    private $samples;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getSamplesTitle'])
            ->onlyMethods(['getId', 'getTypeId'])
            ->getMockForAbstractClass();
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->downloadableFileMock = $this->createMock(DownloadableFile::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->samples = $this->objectManagerHelper->getObject(
            Samples::class,
            [
                'escaper' => $this->escaperMock,
                'locator' => $this->locatorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'downloadableFile' => $this->downloadableFileMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    /**
     * Test getSamplesTitle()
     *
     * @param int|null $id
     * @param string $typeId
     * @param InvokedCount $expectedGetTitle
     * @param InvokedCount $expectedGetValue
     * @return void
     * @dataProvider getSamplesTitleDataProvider
     */
    public function testGetSamplesTitle($id, $typeId, $expectedGetTitle, $expectedGetValue)
    {
        $title = 'My Title';
        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn($typeId);
        $this->productMock->expects($expectedGetTitle)
            ->method('getSamplesTitle')
            ->willReturn($title);
        $this->scopeConfigMock->expects($expectedGetValue)
            ->method('getValue')
            ->willReturn($title);

        /* Assert Result */
        $this->assertEquals($title, $this->samples->getSamplesTitle());
    }

    /**
     * @return array
     */
    public static function getSamplesTitleDataProvider()
    {
        return [
            [
                'id' => 1,
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'expectedGetTitle' => self::once(),
                'expectedGetValue' => self::never(),
            ],
            [
                'id' => null,
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'expectedGetTitle' => self::never(),
                'expectedGetValue' => self::once(),
            ],
            [
                'id' => 1,
                'typeId' => 'someType',
                'expectedGetTitle' => self::never(),
                'expectedGetValue' => self::once(),
            ],
            [
                'id' => null,
                'typeId' => 'someType',
                'expectedGetTitle' => self::never(),
                'expectedGetValue' => self::once(),
            ],
        ];
    }
}
