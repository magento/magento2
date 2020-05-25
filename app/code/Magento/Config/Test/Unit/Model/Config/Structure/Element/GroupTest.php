<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use Magento\Config\Model\Config\BackendClone\Factory;
use Magento\Config\Model\Config\Structure\Element\Dependency\Mapper;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_cloneFactoryMock;

    /**
     * @var MockObject
     */
    protected $_depMapperMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_cloneFactoryMock = $this->createMock(Factory::class);
        $this->_depMapperMock = $this->createMock(
            Mapper::class
        );

        $this->_model = $objectManager->getObject(
            Group::class,
            [
                'cloneModelFactory' => $this->_cloneFactoryMock,
                'dependencyMapper' => $this->_depMapperMock,
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_cloneFactoryMock);
        unset($this->_depMapperMock);
    }

    public function testShouldCloneFields()
    {
        $this->assertFalse($this->_model->shouldCloneFields());
        $this->_model->setData(['clone_fields' => 1], 'scope');
        $this->assertTrue($this->_model->shouldCloneFields());
        $this->_model->setData(['clone_fields' => 0], 'scope');
        $this->assertFalse($this->_model->shouldCloneFields());
        $this->_model->setData(['clone_fields' => false], 'scope');
        $this->assertFalse($this->_model->shouldCloneFields());
    }

    public function testGetCloneModelThrowsExceptionIfNoSourceModelIsSet()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->_model->getCloneModel();
    }

    public function testGetCloneModelCreatesCloneModel()
    {
        $cloneModel = $this->getMockForAbstractClass(ValueInterface::class);
        $this->_depMapperMock = $this->createMock(
            Mapper::class
        );
        $this->_cloneFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'clone_model_name'
        )->willReturn(
            $cloneModel
        );
        $this->_model->setData(['clone_model' => 'clone_model_name'], 'scope');
        $this->assertEquals($cloneModel, $this->_model->getCloneModel());
    }

    public function testGetFieldsetSetsOnlyNonArrayValuesToFieldset()
    {
        $fieldsetMock = $this->getMockBuilder(Fieldset::class)
            ->addMethods(['setOriginalData'])
            ->disableOriginalConstructor()
            ->getMock();
        $fieldsetMock->expects(
            $this->once()
        )->method(
            'setOriginalData'
        )->with(
            ['var1' => 'val1', 'var2' => 'val2']
        );

        $this->_model->setData(['var1' => 'val1', 'var2' => 'val2', 'var3' => ['val3']], 'scope');
        $this->_model->populateFieldset($fieldsetMock);
    }

    public function testIsExpanded()
    {
        $this->assertFalse($this->_model->isExpanded());
        $this->_model->setData(['expanded' => 1], 'scope');
        $this->assertTrue($this->_model->isExpanded());
        $this->_model->setData(['expanded' => 0], 'scope');
        $this->assertFalse($this->_model->isExpanded());
        $this->_model->setData(['expanded' => null], 'scope');
        $this->assertFalse($this->_model->isExpanded());
    }

    public function testGetFieldsetCss()
    {
        $this->assertEquals('', $this->_model->getFieldsetCss());
        $this->_model->setData(['fieldset_css' => 'some_css'], 'scope');
        $this->assertEquals('some_css', $this->_model->getFieldsetCss());
    }

    public function testGetDependenciesWithoutDependencies()
    {
        $this->_depMapperMock->expects($this->never())->method('getDependencies');
    }

    public function testGetDependenciesWithDependencies()
    {
        $fields = [
            'field_4' => [
                'id' => 'section_2/group_3/field_4',
                'value' => 'someValue',
                'dependPath' => ['section_2', 'group_3', 'field_4'],
            ],
        ];
        $this->_model->setData(['depends' => ['fields' => $fields]], 0);
        $this->_depMapperMock->expects(
            $this->once()
        )->method(
            'getDependencies'
        )->with(
            $fields,
            'test_scope'
        )->willReturnArgument(
            0
        );

        $this->assertEquals($fields, $this->_model->getDependencies('test_scope'));
    }
}
