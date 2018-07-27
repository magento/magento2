<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    public function testViewedProductsWidget()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Widget\Instance'
        );
        $config = $model->setType('Magento\Reports\Block\Product\Widget\Viewed')->getWidgetConfigAsArray();

        $this->assertArrayHasKey('parameters', $config);
        $templates = $config['parameters'];
        $this->assertArrayHasKey('template', $templates);
        $templates = $templates['template'];
        $this->assertArrayHasKey('values', $templates);
        $templates = $templates['values'];

        $this->assertArrayHasKey('default', $templates);
        $this->assertArrayHasKey('list', $templates);
        $this->assertArrayHasKey('list_default', $templates);
        $this->assertArrayHasKey('list_names', $templates);
        $this->assertArrayHasKey('list_images', $templates);

        $this->assertArrayHasKey('supported_containers', $config);
        $blocks = $config['supported_containers'];

        $containers = [];
        foreach ($blocks as $block) {
            $containers[] = $block['container_name'];
        }

        $this->assertContains('sidebar.main', $containers);
        $this->assertContains('content', $containers);
        $this->assertContains('sidebar.additional', $containers);
    }

    public function testComparedProductsWidget()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Widget\Instance'
        );
        $config = $model->setType('Magento\Reports\Block\Product\Widget\Compared')->getWidgetConfigAsArray();

        $this->assertArrayHasKey('parameters', $config);
        $templates = $config['parameters'];
        $this->assertArrayHasKey('template', $templates);
        $templates = $templates['template'];
        $this->assertArrayHasKey('values', $templates);
        $templates = $templates['values'];

        $this->assertArrayHasKey('default', $templates);
        $this->assertArrayHasKey('list', $templates);
        $this->assertArrayHasKey('list_default', $templates);
        $this->assertArrayHasKey('list_names', $templates);
        $this->assertArrayHasKey('list_images', $templates);

        $this->assertArrayHasKey('supported_containers', $config);
        $blocks = $config['supported_containers'];
        $containers = [];
        foreach ($blocks as $block) {
            $containers[] = $block['container_name'];
        }

        $this->assertContains('sidebar.main', $containers);
        $this->assertContains('content', $containers);
        $this->assertContains('sidebar.additional', $containers);
    }
}
