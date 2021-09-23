<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Modifier\AddUrlToName;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AddUrlToNameTest
 * @package Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Modifier
 */
class AddUrlToNameTest extends TestCase
{
    /**
     * Sample product url
     */
    const SAMPLE_PRODUCT_URL = 'Sample URL';

    /**
     * Sample product name
     */
    const SAMPLE_PRODUCT_NAME = 'Sample product';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn(self::SAMPLE_PRODUCT_URL);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturn(self::SAMPLE_PRODUCT_NAME);
    }

    /**
     * Test checks AddUrlToName type
     */
    public function testCheckType(): void
    {
        $this->assertInstanceOf(AddUrlToName::class, $this->getModel());
    }

    /**
     * Test checks modifyData method
     *
     * @param array $expectedData
     * @param array $providedData
     * @dataProvider getDataDataProvider
     */
    public function testModifyData(array $expectedData, array $providedData): void
    {
        $this->assertSame($expectedData, $this->getModel()->modifyData($providedData));
    }

    /**
     * @return array
     */
    public function getDataDataProvider(): array
    {
        return [
            0 => [
                'expectedData' => [
                    'items' => [
                        [
                        'entity_id' => '1',
                        'name' =>
                            '<a href="javascript:;" onclick="window.open(\'Sample URL\', \'_blank\');">Sample product</a>'
                        ]
                    ]
                ],
                'providedData' => [
                    'items' => [
                        [
                            'entity_id' => '1',
                            'name' => 'Sample product'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return object
     */
    private function getModel(): AddUrlToName
    {
        return $this->objectManager->getObject(AddUrlToName::class, [
            'urlBuilder' => $this->urlBuilderMock,
            'escaper' => $this->escaperMock,
            'request' => $this->requestMock,
            'storeManager' => $this->storeManagerMock,
        ]);
    }
}
