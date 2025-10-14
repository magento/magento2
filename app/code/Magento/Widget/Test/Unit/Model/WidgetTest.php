<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model;

use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\CatalogWidget\Model\Rule\Condition\Combine;
use Magento\Cms\Block\Widget\Page\Link;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Widget\Helper\Conditions;
use Magento\Widget\Model\Config\Data;
use Magento\Widget\Model\Widget;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Widget\Model\Widget
 */
class WidgetTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $dataStorageMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var Widget
     */
    protected $widget;

    /**
     * @var Conditions
     */
    private $conditionsHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->dataStorageMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->conditionsHelper = $this->getMockBuilder(Conditions::class)
            ->onlyMethods(['encode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->widget = $objectManagerHelper->getObject(
            Widget::class,
            [
                'dataStorage' => $this->dataStorageMock,
                'conditionsHelper' => $this->conditionsHelper,
                'escaper' => $this->escaperMock,
            ]
        );
    }

    /**
     * Unit test for getWidget
     */
    public function testGetWidgets()
    {
        $expected = ['val1', 'val2'];
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn($expected);
        $result = $this->widget->getWidgets();
        $this->assertEquals($expected, $result);
    }

    /**
     * Unit test for getWidgetsWithFilter
     */
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

    /**
     * Unit test for getWidgetsWithUnknownFilter
     */
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

    /**
     * Unit test for getWidgetByClassType
     */
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

    /**
     * Unit test for getConfigAsObject
     */
    public function testGetConfigAsObject()
    {
        $configFile = __DIR__ . '/_files/mappedConfigArrayAll.php';
        $widgets = include $configFile;
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn($widgets);

        $resultObject = $this->widget->getConfigAsObject(Link::class);
        $this->assertInstanceOf(DataObject::class, $resultObject);

        $this->assertSame('CMS Page Link', $resultObject->getName());
        $this->assertSame('Link to a CMS Page', $resultObject->getDescription());
        $this->assertSame('1', $resultObject->getIsEmailCompatible());
        $this->assertSame('Magento_Cms::images/widget_page_link.png', $resultObject->getPlaceholderImage());

        $resultParameters = $resultObject->getParameters();
        $this->assertInstanceOf(DataObject::class, $resultParameters['page_id']);
        $this->assertInstanceOf(DataObject::class, $resultParameters['anchor_text']);
        $this->assertInstanceOf(DataObject::class, $resultParameters['template']);

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

    /**
     * Unit test for getConfigAsObjectWidgetNoFound
     */
    public function testGetConfigAsObjectWidgetNoFound()
    {
        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $resultObject = $this->widget->getConfigAsObject(Link::class);
        $this->assertInstanceOf(DataObject::class, $resultObject);
        $this->assertSame([], $resultObject->getData());
    }

    /**
     * Unit test for getWidgetDeclaration
     */
    public function testGetWidgetDeclaration()
    {
        $mathRandomMock = $this->createPartialMock(Random::class, ['getRandomString']);
        $mathRandomMock->expects($this->any())->method('getRandomString')->willReturn('asdf');
        $reflection = new \ReflectionClass(get_class($this->widget));
        $reflectionProperty = $reflection->getProperty('mathRandom');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->widget, $mathRandomMock);

        $conditions = [
            [
                'type' => Combine::class,
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => ''
            ]
        ];
        $params = [
            'title' => 'my "widget"',
            'show_pager' => '1',
            'products_per_page' => '5',
            'products_count' => '10',
            'template' => 'Magento_CatalogWidget::product/widget/content/grid.phtml',
            'conditions' => $conditions
        ];

        $this->conditionsHelper->expects($this->once())->method('encode')->with($conditions)
            ->willReturn('encoded-conditions-string');
        $this->escaperMock->expects($this->atLeastOnce())
            ->method('escapeHtmlAttr')
            ->willReturnMap([
                ['my "widget"', false, 'my &quot;widget&quot;'],
                ['1', false, '1'],
                ['5', false, '5'],
                ['10', false, '10'],
                ['Magento_CatalogWidget::product/widget/content/grid.phtml',
                    false,
                    'Magento_CatalogWidget::product/widget/content/grid.phtml'
                ],
                ['encoded-conditions-string', false, 'encoded-conditions-string'],
            ]);

        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $result = $this->widget->getWidgetDeclaration(
            ProductsList::class,
            $params
        );
        $this->assertStringContainsString('{{widget type="Magento\CatalogWidget\Block\Product\ProductsList"', $result);
        $this->assertStringContainsString('title="my &quot;widget&quot;"', $result);
        $this->assertStringContainsString('conditions_encoded="encoded-conditions-string"', $result);
        $this->assertStringContainsString('page_var_name="pasdf"', $result);
    }

    /**
     * Unit test for getWidgetDeclarationWithZeroValueParam
     */
    public function testGetWidgetDeclarationWithZeroValueParam()
    {
        $mathRandomMock = $this->createPartialMock(Random::class, ['getRandomString']);
        $mathRandomMock->expects($this->any())
            ->method('getRandomString')
            ->willReturn('asdf');

        (new ObjectManager($this))->setBackwardCompatibleProperty(
            $this->widget,
            'mathRandom',
            $mathRandomMock
        );

        $conditions = [
            [
                'type' => Combine::class,
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => ''
            ]
        ];
        $params = [
            'title' => 'my widget',
            'show_pager' => '1',
            'products_per_page' => '5',
            'products_count' => '0',
            'template' => 'Magento_CatalogWidget::product/widget/content/grid.phtml',
            'conditions' => $conditions
        ];

        $this->conditionsHelper->expects($this->once())
            ->method('encode')
            ->with($conditions)
            ->willReturn('encoded-conditions-string');

        $this->dataStorageMock->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $result = $this->widget->getWidgetDeclaration(
            ProductsList::class,
            $params
        );
        $this->assertStringContainsString('{{widget type="Magento\CatalogWidget\Block\Product\ProductsList"', $result);
        $this->assertStringContainsString('page_var_name="pasdf"', $result);
        $this->assertStringContainsString('products_count=""', $result);
    }
}
