<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Widget'
        );
    }

    public function testGetWidgetsArray()
    {
        $declaredWidgets = $this->_model->getWidgetsArray();
        $this->assertNotEmpty($declaredWidgets);
        $this->assertInternalType('array', $declaredWidgets);
        foreach ($declaredWidgets as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('code', $row);
            $this->assertArrayHasKey('type', $row);
            $this->assertArrayHasKey('description', $row);
        }
    }

    /**
     * @param string $type
     * @param string $expectedFile
     * @return string
     *
     * @dataProvider getPlaceholderImageUrlDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetPlaceholderImageUrl($type, $expectedFile)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $objectManager->get('Magento\Framework\View\DesignInterface')->setDesignTheme('Magento/backend');
        $expectedFilePath = "/adminhtml/Magento/backend/en_US/{$expectedFile}";

        $url = $this->_model->getPlaceholderImageUrl($type);
        $this->assertStringEndsWith($expectedFilePath, $url);
    }

    /**
     * @return array
     */
    public function getPlaceholderImageUrlDataProvider()
    {
        return [
            'custom image' => [
                'Magento\Catalog\Block\Product\Widget\NewWidget',
                'Magento_Catalog/images/product_widget_new.png',
            ],
            'default image' => ['non_existing_widget_type', 'Magento_Widget/placeholder.gif']
        ];
    }
}
