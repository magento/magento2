<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Samples;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;
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
        $this->productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getSamplesTitle', 'getId', 'getTypeId']
        );
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->downloadableFileMock = $this->createMock(DownloadableFile::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
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
     */
    #[DataProvider('getSamplesTitleDataProvider')]
    public function testGetSamplesTitle($id, $typeId, $expectedGetTitle, $expectedGetValue)
    {
        $title = 'My Title';
        $this->locatorMock->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $this->productMock->method('getTypeId')->willReturn($typeId);
        $this->productMock->expects($this->$expectedGetTitle())
            ->method('getSamplesTitle')
            ->willReturn($title);
        $this->scopeConfigMock->expects($this->$expectedGetValue())
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
                'expectedGetTitle' => 'once',
                'expectedGetValue' => 'never',
            ],
            [
                'id' => null,
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'expectedGetTitle' => 'never',
                'expectedGetValue' => 'once',
            ],
            [
                'id' => 1,
                'typeId' => 'someType',
                'expectedGetTitle' => 'never',
                'expectedGetValue' => 'once',
            ],
            [
                'id' => null,
                'typeId' => 'someType',
                'expectedGetTitle' => 'never',
                'expectedGetValue' => 'once',
            ],
        ];
    }
}
