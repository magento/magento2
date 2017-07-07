<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

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
        $this->assertEquals(\Magento\ModuleName\Model\ClassName::class, $this->model->class_test->getClassName());
        $this->assertEquals(\Magento\ModuleName\Model\ClassName::class, $this->model->model_test->getClassName());
        $this->assertFalse($this->model->no_classname_test->getClassName());
    }
}
