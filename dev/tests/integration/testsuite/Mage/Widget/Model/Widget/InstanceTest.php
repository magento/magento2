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
 * @package     Mage_Widget
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Widget
 */
class Mage_Widget_Model_Widget_InstanceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return Mage_Widget_Model_Widget_Instance
     */
    public function testGetWidgetConfig()
    {
        $model = new Mage_Widget_Model_Widget_Instance;
        $config = $model->setType('Mage_Catalog_Block_Product_Widget_New')->getWidgetConfig();
        $this->assertInstanceOf('Varien_Simplexml_Element', $config);
        /** @var Varien_Simplexml_Element $config */
        $element = $config->xpath('/widgets/new_products/parameters/template/values/list');
        $this->assertArrayHasKey(0, $element);
        $this->assertInstanceOf('Varien_Simplexml_Element', $element[0]);
        return $model;
    }

    /**
     * @param Mage_Widget_Model_Widget_Instance $model
     * @depends testGetWidgetConfig
     */
    public function testGenerateLayoutUpdateXml(Mage_Widget_Model_Widget_Instance $model)
    {
        $this->assertEquals('', $model->generateLayoutUpdateXml('content'));
        $model->setId('test_id')->setPackageTheme('default/default');
        $result = $model->generateLayoutUpdateXml('content');
        $this->assertContains('<reference name="content">', $result);
        $this->assertContains('<block type="' . $model->getType() . '"', $result);
    }

    public function testSetGetType()
    {
        $model = new Mage_Widget_Model_Widget_Instance();
        $this->assertEmpty($model->getType());

        $model->setType('test-test');
        $this->assertEquals('test/test', $model->getType());

        $model->setData('instance_type', 'test-test');
        $this->assertEquals('test/test', $model->getType());
    }
}
