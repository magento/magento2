<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Item;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Item\Validator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * Data to be validated
     *
     * @var array
     */
    protected $_params = [
        'id' => 'item',
        'title' => 'Item Title',
        'action' => '/system/config',
        'resource' => 'Magento_Backend::system_config',
        'dependsOnModule' => 'Magento_Backend',
        'dependsOnConfig' => 'system/config/isEnabled',
        'toolTip' => 'Item tooltip',
    ];

    protected function setUp()
    {
        $this->_model = new \Magento\Backend\Model\Menu\Item\Validator();
    }

    /**
     * @param string $requiredParam
     * @throws \BadMethodCallException
     * @expectedException \BadMethodCallException
     * @dataProvider requiredParamsProvider
     */
    public function testValidateWithMissingRequiredParamThrowsException($requiredParam)
    {
        try {
            unset($this->_params[$requiredParam]);
            $this->_model->validate($this->_params);
        } catch (\BadMethodCallException $e) {
            $this->assertContains($requiredParam, $e->getMessage());
            throw $e;
        }
    }

    public function requiredParamsProvider()
    {
        return [['id'], ['title'], ['resource']];
    }

    /**
     * @param string $param
     * @param mixed $invalidValue
     * @throws \InvalidArgumentException
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidParamsProvider
     */
    public function testValidateWithNonValidPrimitivesThrowsException($param, $invalidValue)
    {
        try {
            $this->_params[$param] = $invalidValue;
            $this->_model->validate($this->_params);
        } catch (\InvalidArgumentException $e) {
            $this->assertContains($param, $e->getMessage());
            throw $e;
        }
    }

    public function invalidParamsProvider()
    {
        return [
            ['id', 'ab'],
            ['id', 'abc$'],
            ['title', 'a'],
            ['title', '123456789012345678901234567890123456789012345678901'],
            ['action', '1a'],
            ['action', '12b|'],
            ['resource', '1a'],
            ['resource', '12b|'],
            ['dependsOnModule', '1a'],
            ['dependsOnModule', '12b|'],
            ['dependsOnConfig', '1a'],
            ['dependsOnConfig', '12b|'],
            ['toolTip', 'a'],
            ['toolTip', '123456789012345678901234567890123456789012345678901']
        ];
    }

    /**
     *  Validate duplicated ids
     *
     * @param $existedItems
     * @param $newItem
     * @dataProvider duplicateIdsProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateWithDuplicateIdsThrowsException($existedItems, $newItem)
    {
        foreach ($existedItems as $item) {
            $item = array_merge($item, $this->_params);
            $this->_model->validate($item);
        }

        $newItem = array_merge($newItem, $this->_params);
        $this->_model->validate($newItem);
    }

    /**
     * Provide items with duplicates ids
     *
     * @return array
     */
    public function duplicateIdsProvider()
    {
        return [
            [
                [
                    [
                        'id' => 'item1',
                        'title' => 'Item 1',
                        'action' => 'adminhtml/controller/item1',
                        'resource' => 'Namespace_Module::item1',
                    ],
                    [
                        'id' => 'item2',
                        'title' => 'Item 2',
                        'action' => 'adminhtml/controller/item2',
                        'resource' => 'Namespace_Module::item2'
                    ],
                ],
                [
                    'id' => 'item1',
                    'title' => 'Item 1',
                    'action' => 'adminhtml/controller/item1',
                    'resource' => 'Namespace_Module::item1'
                ],
            ],
            [
                [
                    [
                        'id' => 'Namespace_Module::item1',
                        'title' => 'Item 1',
                        'action' => 'adminhtml/controller/item1',
                        'resource' => 'Namespace_Module::item1',
                    ],
                    [
                        'id' => 'Namespace_Module::item2',
                        'title' => 'Item 2',
                        'action' => 'adminhtml/controller/item2',
                        'resource' => 'Namespace_Module::item1'
                    ],
                ],
                [
                    'id' => 'Namespace_Module::item1',
                    'title' => 'Item 1',
                    'action' => 'adminhtml/controller/item1',
                    'resource' => 'Namespace_Module::item1'
                ]
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateParamWithNullForRequiredParamThrowsException()
    {
        $this->_model->validateParam('title', null);
    }

    public function testValidateParamWithNullForNonRequiredParamDoesntValidate()
    {
        try {
            $this->_model->validateParam('toolTip', null);
        } catch (\Exception $e) {
            $this->fail("Non required null values should not be validated");
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateParamValidatesPrimitiveValues()
    {
        $this->_model->validateParam('toolTip', '/:');
    }

    /**
     * Resources belonging to a module within a compound namespace must pass the validation
     */
    public function testValidateParamResourceCompoundModuleNamespace()
    {
        $this->_model->validateParam('resource', 'TheCompoundNamespace_TheCompoundModule::resource');
    }
}
