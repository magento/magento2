<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Item;

use Magento\Backend\Model\Menu\Item\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_factoryMock;

    /**
     * @var MockObject
     */
    protected $_urlModelMock;

    /**
     * @var MockObject
     */
    protected $_aclMock;

    /**
     * @var MockObject
     */
    protected $_helperMock;

    /**
     * @var MockObject
     */
    protected $_appConfigMock;

    /**
     * @var MockObject
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
        'resource' => 'Magento_Config::system_config',
        'dependsOnModule' => 'Magento_Backend',
        'dependsOnConfig' => 'system/config/isEnabled',
        'toolTip' => 'Item tooltip',
    ];

    protected function setUp(): void
    {
        $this->_model = new Validator();
    }

    /**
     * @param string $requiredParam
     * @throws \BadMethodCallException
     * @dataProvider requiredParamsProvider
     */
    public function testValidateWithMissingRequiredParamThrowsException($requiredParam)
    {
        $this->expectException('BadMethodCallException');
        try {
            unset($this->_params[$requiredParam]);
            $this->_model->validate($this->_params);
        } catch (\BadMethodCallException $e) {
            $this->assertStringContainsString($requiredParam, $e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function requiredParamsProvider()
    {
        return [['id'], ['title'], ['resource']];
    }

    /**
     * @param string $param
     * @param mixed $invalidValue
     * @throws \InvalidArgumentException
     * @dataProvider invalidParamsProvider
     */
    public function testValidateWithNonValidPrimitivesThrowsException($param, $invalidValue)
    {
        $this->expectException('InvalidArgumentException');
        try {
            $this->_params[$param] = $invalidValue;
            $this->_model->validate($this->_params);
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString($param, $e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array
     */
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
     */
    public function testValidateWithDuplicateIdsThrowsException($existedItems, $newItem)
    {
        $this->expectException('InvalidArgumentException');

        foreach ($existedItems as $item) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $item = array_merge($item, $this->_params);
            $this->_model->validate($item);
        }

        $newItem = array_merge($newItem, $this->_params);
        $result = $this->_model->validate($newItem);
        $this->assertNull($result);
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

    public function testValidateParamWithNullForRequiredParamThrowsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->_model->validateParam('title', null);
    }

    public function testValidateParamWithNullForNonRequiredParamDoesntValidate()
    {
        try {
            $result = $this->_model->validateParam('toolTip', null);
            $this->assertNull($result);
        } catch (\Exception $e) {
            $this->fail("Non required null values should not be validated");
        }
    }

    public function testValidateParamValidatesPrimitiveValues()
    {
        $this->expectException('InvalidArgumentException');
        $this->_model->validateParam('toolTip', '/:');
    }

    /**
     * Resources belonging to a module within a compound namespace must pass the validation
     */
    public function testValidateParamResourceCompoundModuleNamespace()
    {
        $result = $this->_model->validateParam('resource', 'TheCompoundNamespace_TheCompoundModule::resource');
        $this->assertNull($result);
    }
}
