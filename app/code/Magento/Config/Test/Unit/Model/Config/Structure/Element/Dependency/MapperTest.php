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
     * @var array
     */
    protected $_testData;

    /**
     * @var MockObject
     */
    protected $_fieldFactoryMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $_scopeConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_testData = [
            'field_x' => ['id' => self::FIELD_ID1],
            'field_y' => ['id' => self::FIELD_ID2]
        ];

        $this->_configStructureMock = $this->getMockBuilder(Structure::class)
            ->onlyMethods(['getElement'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_fieldFactoryMock = $this->getMockBuilder(FieldFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_scopeConfigMock = $this->getMockBuilder(
            ScopeConfigInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = new Mapper(
            $this->_configStructureMock,
            $this->_fieldFactoryMock,
            $this->_scopeConfigMock
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_configStructureMock);
        unset($this->_fieldFactoryMock);
        unset($this->_testData);
    }

    /**
     * @param bool $isValueSatisfy
     *
     * @return void
     * @dataProvider getDependenciesDataProvider
     */
    public function testGetDependenciesWhenDependentIsInvisible($isValueSatisfy): void
    {
        $expected = [];
        $rowData = array_values($this->_testData);
        $count = count($this->_testData);

        $configStructureMockWithArgs = $configStructureMockWillReturnArgs = [];
        $fieldFactoryMockWithArgs = $fieldFactoryMockWillReturnArgs = [];
        $scopeConfigMockWithArgs = $scopeConfigMockWillReturnArgs = [];

        for ($i = 0; $i < $count; ++$i) {
            $data = $rowData[$i];
            $dependentPath = 'some path ' . $i;
            $field = $this->_getField(
                false,
                $dependentPath,
                'Magento_Backend_Model_Config_Structure_Element_Field_' . (string)$isValueSatisfy . $i
            );
            $dependencyField = $this->_getDependencyField(
                $isValueSatisfy,
                false,
                $data['id'],
                'Magento_Backend_Model_Config_Structure_Element_Dependency_Field_' . (string)$isValueSatisfy . $i
            );
            $configStructureMockWithArgs[] = [$data['id']];
            $configStructureMockWillReturnArgs[] = $field;
            $fieldFactoryMockWithArgs[] = [['fieldData' => $data, 'fieldPrefix' => self::FIELD_PREFIX]];
            $fieldFactoryMockWillReturnArgs[] = $dependencyField;
            $scopeConfigMockWithArgs[] = [$dependentPath, ScopeInterface::SCOPE_STORE, self::STORE_CODE];
            $scopeConfigMockWillReturnArgs[] = self::VALUE_IN_STORE;

            if (!$isValueSatisfy) {
                $expected[$data['id']] = $dependencyField;
            }
        }
        $this->_configStructureMock
            ->method('getElement')
            ->willReturnCallback(function ($configStructureMockWithArgs) use ($configStructureMockWillReturnArgs) {
                static $callCount = 0;
                $returnValue = $configStructureMockWillReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });
        $this->_fieldFactoryMock
            ->method('create')
            ->willReturnCallback(function ($fieldFactoryMockWithArgs) use ($fieldFactoryMockWillReturnArgs) {
                static $callCount = 0;
                $returnValue = $fieldFactoryMockWillReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });
        $this->_scopeConfigMock->method('getValue')
            ->willReturnCallback(function ($scopeConfigMockWithArgs) use ($scopeConfigMockWillReturnArgs) {
                static $callCount = 0;
                $returnValue = $scopeConfigMockWillReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });

        $actual = $this->_model->getDependencies($this->_testData, self::STORE_CODE, self::FIELD_PREFIX);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function getDependenciesDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * @return void
     */
    public function testGetDependenciesIsVisible(): void
    {
        $expected = [];
        $rowData = array_values($this->_testData);
        $count = count($this->_testData);
        $configStructureMockWithArgs = $configStructureMockWillReturnArgs = [];
        $fieldFactoryMockWithArgs = $fieldFactoryMockWillReturnArgs = [];

        for ($i = 0; $i < $count; ++$i) {
            $data = $rowData[$i];
            $field = $this->_getField(
                true,
                'some path',
                'Magento_Backend_Model_Config_Structure_Element_Field_visible_' . $i
            );
            $dependencyField = $this->_getDependencyField(
                (bool)$i,
                true,
                $data['id'],
                'Magento_Backend_Model_Config_Structure_Element_Dependency_Field_visible_' . $i
            );
            $configStructureMockWithArgs[] = [$data['id']];
            $configStructureMockWillReturnArgs[] = $field;
            $fieldFactoryMockWithArgs[] = [['fieldData' => $data, 'fieldPrefix' => self::FIELD_PREFIX]];
            $fieldFactoryMockWillReturnArgs[] = $dependencyField;

            $expected[$data['id']] = $dependencyField;
        }
        $this->_configStructureMock
            ->method('getElement')
            ->willReturnCallback(function (...$args)
 use ($configStructureMockWithArgs, $configStructureMockWillReturnArgs) {
                $index = array_search($args, $configStructureMockWithArgs);
                if ($index !== false) {
                    return $configStructureMockWillReturnArgs[$index];
                } else {
                    return null;
                }
            });
        $this->_fieldFactoryMock
            ->method('create')
            ->willReturnCallback(function ($fieldFactoryMockWithArgs) use ($fieldFactoryMockWillReturnArgs) {
                static $callCount = 0;
                $returnValue = $fieldFactoryMockWillReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });

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
     *
     * @return MockObject
     */
    protected function _getDependencyField($isValueSatisfy, $isFieldVisible, $fieldId, $mockClassName): MockObject
    {
        $field = $this->getMockBuilder(Field::class)
            ->onlyMethods(['isValueSatisfy', 'getId'])
            ->setMockClassName($mockClassName)
            ->disableOriginalConstructor()
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
     *
     * @return MockObject
     */
    protected function _getField($isVisible, $path, $mockClassName): MockObject
    {
        $field = $this->getMockBuilder(\Magento\Config\Model\Config\Structure\Element\Field::class)
            ->onlyMethods(['isVisible', 'getPath'])
            ->setMockClassName($mockClassName)
            ->disableOriginalConstructor()
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
