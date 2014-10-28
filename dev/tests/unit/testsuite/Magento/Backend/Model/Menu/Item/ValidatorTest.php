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
    protected $_params = array(
        'id' => 'item',
        'title' => 'Item Title',
        'action' => '/system/config',
        'resource' => 'Magento_Backend::system_config',
        'dependsOnModule' => 'Magento_Backend',
        'dependsOnConfig' => 'system/config/isEnabled',
        'toolTip' => 'Item tooltip'
    );

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
        return array(array('id'), array('title'), array('resource'));
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
        return array(
            array('id', 'ab'),
            array('id', 'abc$'),
            array('title', 'a'),
            array('title', '123456789012345678901234567890123456789012345678901'),
            array('action', '1a'),
            array('action', '12b|'),
            array('resource', '1a'),
            array('resource', '12b|'),
            array('dependsOnModule', '1a'),
            array('dependsOnModule', '12b|'),
            array('dependsOnConfig', '1a'),
            array('dependsOnConfig', '12b|'),
            array('toolTip', 'a'),
            array('toolTip', '123456789012345678901234567890123456789012345678901')
        );
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
        return array(
            array(
                array(
                    array(
                        'id' => 'item1',
                        'title' => 'Item 1',
                        'action' => 'adminhtml/controller/item1',
                        'resource' => 'Namespace_Module::item1'
                    ),
                    array(
                        'id' => 'item2',
                        'title' => 'Item 2',
                        'action' => 'adminhtml/controller/item2',
                        'resource' => 'Namespace_Module::item2'
                    )
                ),
                array(
                    'id' => 'item1',
                    'title' => 'Item 1',
                    'action' => 'adminhtml/controller/item1',
                    'resource' => 'Namespace_Module::item1'
                )
            ),
            array(
                array(
                    array(
                        'id' => 'Namespace_Module::item1',
                        'title' => 'Item 1',
                        'action' => 'adminhtml/controller/item1',
                        'resource' => 'Namespace_Module::item1'
                    ),
                    array(
                        'id' => 'Namespace_Module::item2',
                        'title' => 'Item 2',
                        'action' => 'adminhtml/controller/item2',
                        'resource' => 'Namespace_Module::item1'
                    )
                ),
                array(
                    'id' => 'Namespace_Module::item1',
                    'title' => 'Item 1',
                    'action' => 'adminhtml/controller/item1',
                    'resource' => 'Namespace_Module::item1'
                )
            )
        );
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
