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
 * @category    Magento
 * @package     Mage_Report
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Report_WidgetTest extends PHPUnit_Framework_TestCase
{
    public function testViewedProductsWidget()
    {
        $model = new Mage_Widget_Model_Widget_Instance;
        $config = $model->setType('Mage_Reports_Block_Product_Widget_Viewed')->getWidgetConfig();
        $templates = $config->xpath('parameters/template/values');
        $templates = (array) $templates[0]->children();
        $this->assertArrayHasKey('default', $templates);
        $this->assertArrayHasKey('list', $templates);
        $this->assertArrayHasKey('list_default', $templates);
        $this->assertArrayHasKey('list_names', $templates);
        $this->assertArrayHasKey('list_images', $templates);

        $blocks = $config->xpath('supported_containers');
        $blocks = (array) $blocks[0]->children();
        $this->assertArrayHasKey('left_column', $blocks);
        $this->assertArrayHasKey('main_content', $blocks);
        $this->assertArrayHasKey('right_column', $blocks);
    }

    public function testComparedProductsWidget()
    {
        $model = new Mage_Widget_Model_Widget_Instance;
        $config = $model->setType('Mage_Reports_Block_Product_Widget_Compared')->getWidgetConfig();
        $templates = $config->xpath('parameters/template/values');
        $templates = (array) $templates[0]->children();
        $this->assertArrayHasKey('default', $templates);
        $this->assertArrayHasKey('list', $templates);
        $this->assertArrayHasKey('list_default', $templates);
        $this->assertArrayHasKey('list_names', $templates);
        $this->assertArrayHasKey('list_images', $templates);

        $blocks = $config->xpath('supported_containers');
        $blocks = (array) $blocks[0]->children();
        $this->assertArrayHasKey('left_column', $blocks);
        $this->assertArrayHasKey('main_content', $blocks);
        $this->assertArrayHasKey('right_column', $blocks);
    }

}
