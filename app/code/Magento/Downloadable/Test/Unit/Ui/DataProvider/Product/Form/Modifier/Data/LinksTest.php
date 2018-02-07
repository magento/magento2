<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Links;
use \Magento\Framework\Escaper;
use Magento\Downloadable\Model\Product\Type;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\UrlInterface;
use Magento\Downloadable\Model\Link as LinkModel;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class LinksTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinksTest extends \PHPUnit_Framework_TestCase
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
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var DownloadableFile|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $downloadableFileMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var LinkModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkModelMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

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
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getLinksTitle', 'getId', 'getTypeId'])
            ->getMockForAbstractClass();
        $this->locatorMock = $this->getMock(LocatorInterface::class);
        $this->scopeConfigMock = $this->getMock(ScopeConfigInterface::class);
        $this->escaperMock = $this->getMock(Escaper::class, [], [], '', false);
        $this->downloadableFileMock = $this->getMock(DownloadableFile::class, [], [], '', false);
        $this->urlBuilderMock = $this->getMock(UrlInterface::class);
        $this->linkModelMock = $this->getMock(LinkModel::class, [], [], '', false);
        $this->links = $this->objectManagerHelper->getObject(
            Links::class,
            [
                'escaper' => $this->escaperMock,
                'locator' => $this->locatorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'downloadableFile' => $this->downloadableFileMock,
                'urlBuilder' => $this->urlBuilderMock,
                'linkModel' => $this->linkModelMock,
            ]
        );
    }

    /**
     * @param int|null $id
     * @param string $typeId
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectedGetTitle
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectedGetValue
     * @return void
     * @dataProvider getLinksTitleDataProvider
     */
    public function testGetLinksTitle($id, $typeId, $expectedGetTitle, $expectedGetValue)
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
            ->method('getLinksTitle')
            ->willReturn($title);
        $this->scopeConfigMock->expects($expectedGetValue)
            ->method('getValue')
            ->willReturn($title);

        $this->assertEquals($title, $this->links->getLinksTitle());
    }

    /**
     * @return array
     */
    public function getLinksTitleDataProvider()
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
