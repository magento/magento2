<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model;

class VariableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Variable\Model\Variable
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Variable\Model\Variable::class
        );
    }

    public function testGetSetStoreId()
    {
        $this->_model->setStoreId(1);
        $this->assertEquals(1, $this->_model->getStoreId());
    }

    public function testLoadByCode()
    {
        $this->_model->setData(['code' => 'test_code', 'name' => 'test_name']);
        $this->_model->save();

        $variable = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Variable\Model\Variable::class
        );
        $variable->loadByCode('test_code');
        $this->assertEquals($this->_model->getName(), $variable->getName());
        $this->_model->delete();
    }

    public function testGetValue()
    {
        $html = '<p>test</p>';
        $text = 'test';
        $this->_model->setData(['code' => 'test_code', 'html_value' => $html, 'plain_value' => $text]);
        $this->assertEquals($html, $this->_model->getValue());
        $this->assertEquals($html, $this->_model->getValue(\Magento\Variable\Model\Variable::TYPE_HTML));
        $this->assertEquals($text, $this->_model->getValue(\Magento\Variable\Model\Variable::TYPE_TEXT));
    }

    public function testValidate()
    {
        $this->assertNotEmpty($this->_model->validate());
        $this->_model->setName('test')->setCode('test');
        $this->assertNotEmpty($this->_model->validate());
        $this->_model->save();
        try {
            $this->assertTrue($this->_model->validate());
            $this->_model->delete();
        } catch (\Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testGetVariablesOptionArray()
    {
        $this->assertEquals([], $this->_model->getVariablesOptionArray());
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection();
        $collection->setStoreId(1);
        $this->assertEquals(1, $collection->getStoreId(), 'Store id setter and getter');

        $collection->addValuesToResult();
        $this->assertStringContainsString('variable_value', (string)$collection->getSelect());
    }

    /**
     * Test to verify that returned by getVariablesOptionArray()
     * custom variable label is HTML escaped.
     */
    public function testGetVariablesOptionArrayWithHtmlLabel()
    {
        $expectedLabel = '&lt;b&gt;HTML Name value&lt;/b&gt;';
        $data = [
            'code' => 'html_name',
            'name' => '<b>HTML Name value</b>'
        ];
        $this->_model->setData($data)->save();
        $actualLabel = current(current($this->_model->getVariablesOptionArray())['label']->getArguments());
        $this->assertEquals($expectedLabel, $actualLabel);
    }
}
