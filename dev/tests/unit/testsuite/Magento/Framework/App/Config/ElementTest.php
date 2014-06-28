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
namespace Magento\Framework\App\Config;

class ElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Element
     */
    protected $model;

    protected function setUp()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/element.xml');
        $this->model = new \Magento\Framework\App\Config\Element($xmlString);
    }

    public function testIs()
    {
        /* @var \Magento\Framework\App\Config\Element $element */
        $element = $this->model->is_test;
        $this->assertTrue($element->is('value_key', 'value'));
        $this->assertTrue($element->is('value_sensitive_key', 'value'));
        $this->assertTrue($element->is('regular_cdata', 'value'));
        $this->assertFalse($element->is('false_key'));
        $this->assertFalse($element->is('empty_cdata'));
        $this->assertFalse($element->is('empty_text'));
        $this->assertTrue($element->is('on_key'));
    }

    public function testGetClassName()
    {
        $this->assertEquals('Magento\Catalog\Model\Observer', $this->model->class_test->getClassName());
        $this->assertEquals('Magento\Catalog\Model\Observer', $this->model->model_test->getClassName());
        $this->assertFalse($this->model->no_classname_test->getClassName());
    }
}
