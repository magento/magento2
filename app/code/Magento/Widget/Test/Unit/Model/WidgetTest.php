<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model;

/**
 * Test class for \Magento\Widget\Model\Widget
 */
class WidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataStorageMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $widget;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    private $conditionsHelper;

    protected function setUp()
    {
        $this->dataStorageMock = $this->getMockBuilder(\Magento\Widget\Model\Config\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->conditionsHelper = $this->getMockBuilder(\Magento\Widget\Helper\Conditions::class)
            ->setMethods(['encode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->widget = $objectManagerHelper->getObject(
            \Magento\Widget\Model\Widget::class,
            [
                'dataStorage' => $this->dataStorageMock,
                'conditionsHelper' => $this->conditionsHelper,
                'escaper' => $this->escaperMock,
            ]
        );
    }

    public function testGetWidgets()
    {
        $expected = ['val1', 'val2'];
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn($expected);
        $result = $this->widget->getWidgets();
        $this->assertEquals($expected, $result);
    }

    public function testGetWidgetsWithFilter()
    {
        $configFile = __DIR__ . '/_files/mappedConfigArrayAll.php';
        $widgets = include $configFile;
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn($widgets);
        $result = $this->widget->getWidgets(['name' => 'CMS Page Link', 'description' => 'Link to a CMS Page']);
        $configFileOne = __DIR__ . '/_files/mappedConfigArray1.php';
        $expected = ['cms_page_link' => include $configFileOne];
        $this->assertEquals($expected, $result);
    }

    public function testGetWidgetsWithUnknownFilter()
    {
        $configFile = __DIR__ . '/_files/mappedConfigArrayAll.php';
        $widgets = include $configFile;
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn($widgets);
        $result = $this->widget->getWidgets(['name' => 'unknown', 'description' => 'unknown']);
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    public function testGetWidgetByClassType()
    {
        $widgetOne = ['@' => ['type' => 'type1']];
        $widgets = ['widget1' => $widgetOne];
        $this->dataStorageMock->expects($this->any())
            ->method('get')
            ->willReturn($widgets);
        $this->assertEquals($widgetOne, $this->widget->getWidgetByClassType('type1'));
        $this->assertNull($this->widget->getWidgetByClassType('type2'));
    }

    public function testGetConfigAsObject()
    {
        $configFile = __DIR__ . '/_files/mappedConfigArrayAll.php';
        $widgets = include $configFile;
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn($widgets);

        $resultObject = $this->widget->getConfigAsObject(\Magento\Cms\Block\Widget\Page\Link::class);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $resultObject);

        $this->assertSame('CMS Page Link', $resultObject->getName());
        $this->assertSame('Link to a CMS Page', $resultObject->getDescription());
        $this->assertSame('1', $resultObject->getIsEmailCompatible());
        $this->assertSame('Magento_Cms::images/widget_page_link.png', $resultObject->getPlaceholderImage());

        $resultParameters = $resultObject->getParameters();
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $resultParameters['page_id' ]);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $resultParameters['anchor_text']);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $resultParameters['template']);

        $supportedContainersExpected = [
            '0' => [
                'container_name' => 'left',
                'template' => ['default' => 'default', 'names_only' => 'link_inline'],
            ],
            '1' => [
                'container_name' => 'content',
                'template' => ['grid' => 'default', 'list' => 'list']
            ],
        ];
        $this->assertSame($supportedContainersExpected, $resultObject->getSupportedContainers());
    }

    public function testGetConfigAsObjectWidgetNoFound()
    {
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $resultObject = $this->widget->getConfigAsObject(\Magento\Cms\Block\Widget\Page\Link::class);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $resultObject);
        $this->assertSame([], $resultObject->getData());
    }
}
