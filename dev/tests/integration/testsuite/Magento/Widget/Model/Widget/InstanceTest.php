<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget;

class InstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Widget\Instance
     */
    private $model;

    protected function setUp()
    {
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Widget\Instance::class
        );
    }

    public function testSetGetType()
    {
        $this->assertEmpty($this->model->getType());
        $this->assertSame('test', $this->model->setType('test')->getType());
        $this->assertSame('test', $this->model->getInstanceType());
    }

    public function testSetThemeId()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode('frontend');
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDefaultDesignTheme()->getDesignTheme();
        $this->model->setThemeId($theme->getId());

        $this->assertEquals($theme->getId(), $this->model->getThemeId());
    }

    /**
     * @return \Magento\Widget\Model\Widget\Instance
     */
    public function testGetWidgetConfigAsArray()
    {
        $config = $this->model
            ->setType(\Magento\Catalog\Block\Product\Widget\NewWidget::class)
            ->getWidgetConfigAsArray();
        $this->assertTrue(is_array($config));
        $element = null;
        if (isset(
            $config['parameters']
        ) && isset(
            $config['parameters']['template']
        ) && isset(
            $config['parameters']['template']['values']
        ) && isset(
            $config['parameters']['template']['values']['list']
        )
        ) {
            $element = $config['parameters']['template']['values']['list'];
        }
        $expected = [
            'value' => 'product/widget/new/content/new_list.phtml',
            'label' => 'New Products List Template',
        ];
        $this->assertNotNull($element);
        $this->assertEquals($expected, $element);

        return $this->model;
    }

    /**
     * @return \Magento\Widget\Model\Widget\Instance
     */
    public function testGetWidgetSupportedContainers()
    {
        $this->model->setType(\Magento\Catalog\Block\Product\Widget\NewWidget::class);
        $containers = $this->model->getWidgetSupportedContainers();
        $this->assertInternalType('array', $containers);
        $this->assertContains('sidebar.main', $containers);
        $this->assertContains('content', $containers);
        $this->assertContains('sidebar.additional', $containers);
        return $this->model;
    }

    /**
     * @param \Magento\Widget\Model\Widget\Instance $model
     * @depends testGetWidgetSupportedContainers
     */
    public function testGetWidgetSupportedTemplatesByContainer($model)
    {
        $templates = $model->getWidgetSupportedTemplatesByContainer('content');
        $this->assertNotEmpty($templates);
        $this->assertInternalType('array', $templates);
        foreach ($templates as $row) {
            $this->assertArrayHasKey('value', $row);
            $this->assertArrayHasKey('label', $row);
        }
    }

    /**
     * @param \Magento\Widget\Model\Widget\Instance $model
     * @depends testGetWidgetConfigAsArray
     */
    public function testGenerateLayoutUpdateXmlWhenWidgetParametersIsArray(
        \Magento\Widget\Model\Widget\Instance $model
    ) {
        $params = [
            'display_mode' => 'fixed',
            'types' => ['type_1', 'type_2'],
            'conditions' => [
                '1' => [
                    'type' => 'Magento\CatalogWidget\Model\Rule\Condition\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => [
                    'type' => 'Magento\CatalogWidget\Model\Rule\Condition\Product',
                    'attribute' => 'attribute_set_id',
                    'value' => '4',
                    'operator' => '==',
                ],
            ],
        ];
        $model->setData('widget_parameters', $params);
        $this->assertEquals('', $model->generateLayoutUpdateXml('content'));
        $model->setId('test_id')->setPackageTheme('Magento/luma');
        $result = $model->generateLayoutUpdateXml('content');
        $this->assertContains('<body><referenceContainer name="content">', $result);
        $this->assertContains('<block class="' . $model->getType() . '"', $result);
        $this->assertEquals(count($params), substr_count($result, '<action method="setData">'));
        $this->assertContains('<argument name="name" xsi:type="string">display_mode</argument>', $result);
        $this->assertContains('<argument name="value" xsi:type="string">fixed</argument>', $result);
        $this->assertContains('<argument name="name" xsi:type="string">types</argument>', $result);
        $this->assertContains('<argument name="value" xsi:type="string">type_1,type_2</argument>', $result);
        $this->assertContains('<argument name="name" xsi:type="string">conditions_encoded</argument>', $result);
        $this->assertContains('s:50:`Magento|CatalogWidget|Model|Rule|Condition|Combine`', $result);
        $this->assertContains('s:50:`Magento|CatalogWidget|Model|Rule|Condition|Product`', $result);
    }

    /**
     * @param $widgetParameters
     * @param \Magento\Widget\Model\Widget\Instance $model
     * @dataProvider generateLayoutUpdateXmlWhenWidgetParametersIsNotArrayDataProvider
     * @depends testGetWidgetConfigAsArray
     */
    public function testGenerateLayoutUpdateXmlWhenWidgetParametersIsNotArray(
        $widgetParameters,
        \Magento\Widget\Model\Widget\Instance $model
    ) {
        $model->setData('widget_parameters', $widgetParameters);
        $model->setId('test_id')->setPackageTheme('Magento/luma');
        $result = $model->generateLayoutUpdateXml('content');
        $this->assertContains('<body><referenceContainer name="content">', $result);
        $this->assertContains('<block class="' . $model->getType() . '"', $result);
        $this->assertEquals(0, substr_count($result, '<action method="setData">'));
        $this->assertNotContains('<argument name="name" xsi:type="string">', $result);
        $this->assertNotContains('<argument name="value" xsi:type="string">', $result);
    }

    /**
     * @return array
     */
    public function generateLayoutUpdateXmlWhenWidgetParametersIsNotArrayDataProvider()
    {
        $widgetParameters = [
            'display_mode' => 'fixed',
            'types' => ['type_1', 'type_2'],
            'conditions' => [
                '1' => [
                    'type' => 'Magento\CatalogWidget\Model\Rule\Condition\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => [
                    'type' => 'Magento\CatalogWidget\Model\Rule\Condition\Product',
                    'attribute' => 'attribute_set_id',
                    'value' => '4',
                    'operator' => '==',
                ],
            ],
        ];
        return [
            [''],
            [serialize($widgetParameters)],
            [null],
            [new \stdClass()],
        ];
    }
}
