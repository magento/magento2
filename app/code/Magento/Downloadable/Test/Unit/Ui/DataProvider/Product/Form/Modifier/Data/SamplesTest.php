<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Samples;
use \Magento\Framework\Escaper;
use Magento\Downloadable\Model\Product\Type;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Test class to cover Sample Modifier
 *
 * Class \Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data\SampleTest
 */
class SamplesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LocatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $locatorMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $escaperMock;

    /**
     * @var DownloadableFile|\PHPUnit\Framework\MockObject\MockObject
     */
    private $downloadableFileMock;

    /**
     * @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ProductInterface|\PHPUnit\Framework\MockObject\MockObject
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
            ->setMethods(['getSamplesTitle', 'getId', 'getTypeId'])
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
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $expectedGetTitle
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $expectedGetValue
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
    public function getSamplesTitleDataProvider()
    {
        return [
            [
                'id' => 1,
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'expectedGetTitle' => $this->once(),
                'expectedGetValue' => $this->never(),
            ],
            [
                'id' => null,
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'expectedGetTitle' => $this->never(),
                'expectedGetValue' => $this->once(),
            ],
            [
                'id' => 1,
                'typeId' => 'someType',
                'expectedGetTitle' => $this->never(),
                'expectedGetValue' => $this->once(),
            ],
            [
                'id' => null,
                'typeId' => 'someType',
                'expectedGetTitle' => $this->never(),
                'expectedGetValue' => $this->once(),
            ],
        ];
    }
}
