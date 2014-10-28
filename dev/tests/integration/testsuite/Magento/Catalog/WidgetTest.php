<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    public function testNewProductsWidget()
    {
        $type = 'Magento\Catalog\Block\Product\Widget\NewWidget';

        /** @var $model \Magento\Widget\Model\Widget\Instance */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Widget\Instance'
        );
        $config = $model->setType($type)->getWidgetConfigAsArray();
        $templates = $config['parameters']['template']['values'];
        $this->assertArrayHasKey('default', $templates);
        $this->assertArrayHasKey('list', $templates);
        $this->assertArrayHasKey('list_default', $templates);
        $this->assertArrayHasKey('list_names', $templates);
        $this->assertArrayHasKey('list_images', $templates);

        $blocks = $config['supported_containers'];

        $containers = array();
        foreach ($blocks as $block) {
            $containers[] = $block['container_name'];
        }

        $this->assertContains('sidebar.main', $containers);
        $this->assertContains('content', $containers);
        $this->assertContains('sidebar.additional', $containers);

        // Verify that the correct id (code) is found for this widget instance type.
        $code = $model->setType($type)->getWidgetReference('type', $type, 'code');
        $this->assertEquals('new_products', $code);
    }
}
