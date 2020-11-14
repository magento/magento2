<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element\Dependency;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Dependency\Field;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Config\Model\Config\Structure\Element\Dependency\Mapper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    private const FIELD_PREFIX = 'prefix_';
    private const VALUE_IN_STORE = 'value in store';
    private const FIELD_ID1 = 'field id 1';
    private const FIELD_ID2 = 'field id 2';
    private const STORE_CODE = 'some store code';

    /**
     * @var Mapper
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_configStructureMock;

    /**
     * Test data
     *
     * @var array
     */
    protected $_testData;

    /**
     * Mock of dependency field factory
     *
     * @var MockObject
     */
    protected $_fieldFactoryMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $_scopeConfigMock;

    protected function setUp(): void
    {
        $this->_testData = [
            'field_x' => ['id' => self::FIELD_ID1],
            'field_y' => ['id' => self::FIELD_ID2],
        ];

        $this->_configStructureMock = $this->getMockBuilder(
            Structure::class
        )->setMethods(
            ['getElement']
        )->disableOriginalConstructor()
            ->getMock();
        $this->_fieldFactoryMock = $this->getMockBuilder(
            FieldFactory::class
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()
            ->getMock();
        $this->_scopeConfigMock = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_model = new Mapper(
            $this->_configStructureMock,
            $this->_fieldFactoryMock,
            $this->_scopeConfigMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_configStructureMock);
        unset($this->_fieldFactoryMock);
        unset($this->_testData);
    }

    /**
     * @param bool $isValueSatisfy
     * @dataProvider getDependenciesDataProvider
     */
    public function testGetDependenciesWhenDependentIsInvisible($isValueSatisfy)
    {
        $expected = [];
        $rowData = array_values($this->_testData);
        $count = count($this->_testData);
        for ($i = 0; $i < $count; ++$i) {
            $data = $rowData[$i];
            $dependentPath = 'some path ' . $i;
            $field = $this->_getField(
                false,
                $dependentPath,
                'Magento_Backend_Model_Config_Structure_Element_Field_' . (string)$isValueSatisfy . $i
            );
            $this->_configStructureMock->expects(
                $this->at($i)
            )->method(
                'getElement'
            )->with(
                $data['id']
            )->willReturn(
                $field
            );
            $dependencyField = $this->_getDependencyField(
                $isValueSatisfy,
                false,
                $data['id'],
                'Magento_Backend_Model_Config_Structure_Element_Dependency_Field_' . (string)$isValueSatisfy . $i
            );
            $this->_fieldFactoryMock->expects(
                $this->at($i)
            )->method(
                'create'
            )->with(
                ['fieldData' => $data, 'fieldPrefix' => self::FIELD_PREFIX]
            )->willReturn(
                $dependencyField
            );
            $this->_scopeConfigMock->expects(
                $this->at($i)
            )->method(
                'getValue'
            )->with(
                $dependentPath,
                ScopeInterface::SCOPE_STORE,
                self::STORE_CODE
            )->willReturn(
                self::VALUE_IN_STORE
            );
            if (!$isValueSatisfy) {
                $expected[$data['id']] = $dependencyField;
            }
        }
        $actual = $this->_model->getDependencies($this->_testData, self::STORE_CODE, self::FIELD_PREFIX);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getDependenciesDataProvider()
    {
        return [[true], [false]];
    }

    public function testGetDependenciesIsVisible()
    {
        $expected = [];
        $rowData = array_values($this->_testData);
        $count = count($this->_testData);
        for ($i = 0; $i < $count; ++$i) {
            $data = $rowData[$i];
            $field = $this->_getField(
                true,
                'some path',
                'Magento_Backend_Model_Config_Structure_Element_Field_visible_' . $i
            );
            $this->_configStructureMock->expects(
                $this->at($i)
            )->method(
                'getElement'
            )->with(
                $data['id']
            )->willReturn(
                $field
            );
            $dependencyField = $this->_getDependencyField(
                (bool)$i,
                true,
                $data['id'],
                'Magento_Backend_Model_Config_Structure_Element_Dependency_Field_visible_' . $i
            );
            $this->_fieldFactoryMock->expects(
                $this->at($i)
            )->method(
                'create'
            )->with(
                ['fieldData' => $data, 'fieldPrefix' => self::FIELD_PREFIX]
            )->willReturn(
                $dependencyField
            );
            $expected[$data['id']] = $dependencyField;
        }
        $actual = $this->_model->getDependencies($this->_testData, self::STORE_CODE, self::FIELD_PREFIX);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Get dependency field mock
     *
     * @param bool $isValueSatisfy
     * @param bool $isFieldVisible
     * @param string $fieldId
     * @param string $mockClassName
     * @return MockObject
     */
    protected function _getDependencyField($isValueSatisfy, $isFieldVisible, $fieldId, $mockClassName)
    {
        $field = $this->getMockBuilder(
            Field::class
        )->setMethods(
            ['isValueSatisfy', 'getId']
        )->setMockClassName(
            $mockClassName
        )->disableOriginalConstructor()
            ->getMock();
        if ($isFieldVisible) {
            $field->expects($isFieldVisible ? $this->never() : $this->once())->method('isValueSatisfy');
        } else {
            $field->expects(
                $this->once()
            )->method(
                'isValueSatisfy'
            )->with(
                self::VALUE_IN_STORE
            )->willReturn(
                $isValueSatisfy
            );
        }
        $field->expects(
            $isFieldVisible || !$isValueSatisfy ? $this->once() : $this->never()
        )->method(
            'getId'
        )->willReturn(
            $fieldId
        );
        return $field;
    }

    /**
     * Get field mock
     *
     * @param bool $isVisible
     * @param string $path
     * @param string $mockClassName
     * @return MockObject
     */
    protected function _getField($isVisible, $path, $mockClassName)
    {
        $field = $this->getMockBuilder(
            \Magento\Config\Model\Config\Structure\Element\Field::class
        )->setMethods(
            ['isVisible', 'getPath']
        )->setMockClassName(
            $mockClassName
        )->disableOriginalConstructor()
            ->getMock();
        $field->expects($this->once())->method('isVisible')->willReturn($isVisible);
        if ($isVisible) {
            $field->expects($this->never())->method('getPath');
        } else {
            $field->expects(
                $isVisible ? $this->never() : $this->once()
            )->method(
                'getPath'
            )->with(
                self::FIELD_PREFIX
            )->willReturn(
                $path
            );
        }
        return $field;
    }
}
