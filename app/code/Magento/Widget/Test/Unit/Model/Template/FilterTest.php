<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\Template;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Widget\Model\Template\Filter
     */
    protected $filter;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $widgetResourceMock;

    /**
     * @var \Magento\Widget\Model\Widget|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $widgetMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->widgetResourceMock = $this->createMock(\Magento\Widget\Model\ResourceModel\Widget::class);
        $this->widgetMock = $this->createMock(\Magento\Widget\Model\Widget::class);
        $this->layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);

        $this->filter = $this->objectManagerHelper->getObject(
            \Magento\Widget\Model\Template\Filter::class,
            [
                'storeManager' => $this->storeManagerMock,
                'widgetResource' => $this->widgetResourceMock,
                'widget' => $this->widgetMock,
                'layout' => $this->layoutMock
            ]
        );
    }

    /**
     * @param array $construction
     * @param string $name
     * @param string $type
     * @param int $preConfigId
     * @param array $params
     * @param array $preconfigure
     * @param string $widgetXml
     * @param \Magento\Widget\Block\BlockInterface|null $widgetBlock
     * @param string $expectedResult
     * @return void
     * @dataProvider generateWidgetDataProvider
     */
    public function testGenerateWidget(
        $construction,
        $name,
        $type,
        $preConfigId,
        $params,
        $preconfigure,
        $widgetXml,
        $widgetBlock,
        $expectedResult
    ) {
        $this->generalForGenerateWidget($name, $type, $preConfigId, $params, $preconfigure, $widgetXml, $widgetBlock);
        $this->assertSame($expectedResult, $this->filter->generateWidget($construction));
    }

    /**
     * @param array $construction
     * @param string $name
     * @param string $type
     * @param int $preConfigId
     * @param array $params
     * @param array $preconfigure
     * @param string $widgetXml
     * @param \Magento\Widget\Block\BlockInterface|null $widgetBlock
     * @param string $expectedResult
     * @return void
     * @dataProvider generateWidgetDataProvider
     */
    public function testWidgetDirective(
        $construction,
        $name,
        $type,
        $preConfigId,
        $params,
        $preconfigure,
        $widgetXml,
        $widgetBlock,
        $expectedResult
    ) {
        $this->generalForGenerateWidget($name, $type, $preConfigId, $params, $preconfigure, $widgetXml, $widgetBlock);
        $this->assertSame($expectedResult, $this->filter->widgetDirective($construction));
    }

    /**
     * @return array
     */
    public function generateWidgetDataProvider()
    {
        return [
            [
                'construction' => [
                    '{{widget type="Widget\\Link" anchor_text="Test" template="block.phtml" id_path="p/1"}}',
                    'widget',
                    ' type="" anchor_text="Test" template="block.phtml" id_path="p/1"'
                ],
                'name' => null,
                'type' => 'Widget\Link',
                'preConfigId' => null,
                'params' => ['id' => ''],
                'preconfigure' => [],
                'widgetXml' => '',
                'widgetBlock' => null,
                'expectedResult' => ''
            ],
            [
                'construction' => [
                    '{{widget type="Widget\\Link" anchor_text="Test" template="block.phtml" id_path="p/1"}}',
                    'widget',
                    ' type="" id="1" anchor_text="Test" template="block.phtml" id_path="p/1"'
                ],
                'name' => null,
                'type' => null,
                'preConfigId' => 1,
                'params' => ['id' => '1'],
                'preconfigure' => ['widget_type' => '', 'parameters' => ''],
                'widgetXml' => null,
                'widgetBlock' => null,
                'expectedResult' => ''
            ],
            [
                'construction' => [
                    '{{widget type="Widget\\Link" anchor_text="Test" template="block.phtml" id_path="p/1"}}',
                    'widget',
                    ' type="" name="testName" id="1" anchor_text="Test" template="block.phtml" id_path="p/1"'
                ],
                'name' => 'testName',
                'type' => 'Widget\Link',
                'preConfigId' => 1,
                'params' => ['id' => '1'],
                'preconfigure' => ['widget_type' => "Widget\\Link", 'parameters' => ['id' => '1']],
                'widgetXml' => 'some xml',
                'widgetBlock' => $this->getBlockMock('widget text'),
                'expectedResult' => 'widget text'
            ],
            [
                'construction' => [
                    '{{widget type="Widget\\Link" anchor_text="Test" template="block.phtml" id_path="p/1"}}',
                    'widget',
                    ' type="Widget\\Link" name="testName" anchor_text="Test" template="block.phtml" id_path="p/1"'
                ],
                'name' => 'testName',
                'type' => 'Widget\Link',
                'preConfigId' => null,
                'params' => [
                    'type' => 'Widget\Link',
                    'name' => 'testName',
                    'anchor_text' => 'Test',
                    'template' => 'block.phtml',
                    'id_path' => 'p/1'
                ],
                'preconfigure' => [],
                'widgetXml' => 'some xml',
                'widgetBlock' => $this->getBlockMock('widget text'),
                'expectedResult' => 'widget text'
            ],
        ];
    }

    /**
     * @param string $name
     * @param string $type
     * @param int $preConfigId
     * @param array $params
     * @param array $preconfigure
     * @param string $widgetXml
     * @param \Magento\Widget\Block\BlockInterface|null $widgetBlock
     * @return void
     * @dataProvider generateWidgetDataProvider
     */
    protected function generalForGenerateWidget(
        $name,
        $type,
        $preConfigId,
        $params,
        $preconfigure,
        $widgetXml,
        $widgetBlock
    ) {
        $this->widgetResourceMock->expects($this->any())
            ->method('loadPreconfiguredWidget')
            ->with($preConfigId)
            ->willReturn($preconfigure);
        $this->widgetMock->expects($this->any())
            ->method('getWidgetByClassType')
            ->with($type)
            ->willReturn($widgetXml);
        $this->layoutMock->expects($this->any())
            ->method('createBlock')
            ->with($type, $name, ['data' => $params])
            ->willReturn($widgetBlock);
    }

    /**
     * @param string $returnedResult
     * @return \Magento\Widget\Block\BlockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBlockMock($returnedResult = '')
    {
        /** @var \Magento\Widget\Block\BlockInterface|\PHPUnit_Framework_MockObject_MockObject $blockMock */
        $blockMock = $this->getMockBuilder(\Magento\Widget\Block\BlockInterface::class)
            ->setMethods(['toHtml'])
            ->getMockForAbstractClass();
        $blockMock->expects($this->any())
            ->method('toHtml')
            ->willReturn($returnedResult);

        return $blockMock;
    }

    /**
     * @return void
     */
    public function testMediaDirective()
    {
        $image = 'wysiwyg/VB.png';
        $construction = ['{{media url="' . $image . '"}}', 'media', ' url="' . $image . '"'];
        $baseUrl = 'http://localhost/pub/media/';

        $this->storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $result = $this->filter->mediaDirective($construction);
        $this->assertEquals($baseUrl . $image, $result);
    }

    public function testMediaDirectiveWithEncodedQuotes()
    {
        $image = 'wysiwyg/VB.png';
        $construction = ['{{media url=&quot;' . $image . '&quot;}}', 'media', ' url=&quot;' . $image . '&quot;'];
        $baseUrl = 'http://localhost/pub/media/';

        $this->storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $result = $this->filter->mediaDirective($construction);
        $this->assertEquals($baseUrl . $image, $result);
    }
}
