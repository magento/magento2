<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Config\Test\Unit\Model\Config;

class StructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flyweightFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_tabIteratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_structureDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeDefinerMock;

    /**
     * @var array
     */
    protected $_structureData;

    protected function setUp()
    {
        $this->_flyweightFactory = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\FlyweightFactory::class,
            [],
            [],
            '',
            false
        );
        $this->_tabIteratorMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Iterator\Tab::class,
            [],
            [],
            '',
            false
        );
        $this->_structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );
        $this->_scopeDefinerMock = $this->getMock(
            \Magento\Config\Model\Config\ScopeDefiner::class,
            [],
            [],
            '',
            false
        );
        $this->_scopeDefinerMock->expects($this->any())->method('getScope')->will($this->returnValue('scope'));

        $filePath = dirname(__DIR__) . '/_files';
        $this->_structureData = require $filePath . '/converted_config.php';
        $this->_structureDataMock->expects(
            $this->once()
        )->method(
            'get'
        )->will(
            $this->returnValue($this->_structureData['config']['system'])
        );
        $this->_model = new \Magento\Config\Model\Config\Structure(
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );
    }

    protected function tearDown()
    {
        unset($this->_flyweightFactory);
        unset($this->_scopeDefinerMock);
        unset($this->_structureData);
        unset($this->_tabIteratorMock);
        unset($this->_structureDataMock);
        unset($this->_model);
    }

    public function testGetTabsBuildsSectionTree()
    {
        $this->_structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );
        $this->_structureDataMock->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(
                ['sections' => ['section1' => ['tab' => 'tab1']], 'tabs' => ['tab1' => []]]
            )
        );
        $expected = ['tab1' => ['children' => ['section1' => ['tab' => 'tab1']]]];
        $model = new \Magento\Config\Model\Config\Structure(
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );
        $this->_tabIteratorMock->expects($this->once())->method('setElements')->with($expected);
        $this->assertEquals($this->_tabIteratorMock, $model->getTabs());
    }

    public function testGetSectionList()
    {
        $this->_structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );
        $this->_structureDataMock->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(
                [
                    'sections' => [
                        'section1' => [
                            'children' => [
                                'child_id_1' => 'child_data',
                                'child_id_2' => 'child_data',
                                'child_id_3' => 'child_data'
                            ]
                        ],
                        'section2' => [
                            'children' => [
                                'child_id_1' => 'child_data'
                            ]
                        ],
                    ]
                ]
            )
        );
        $expected = [
            'section1_child_id_1' => true,
            'section1_child_id_2' => true,
            'section1_child_id_3' => true,
            'section2_child_id_1' => true
        ];
        $model = new \Magento\Config\Model\Config\Structure(
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );

        $this->assertEquals($expected, $model->getSectionList());
    }

    /**
     * @param string $path
     * @param string $expectedType
     * @param string $expectedId
     * @param string $expectedPath
     * @dataProvider emptyElementDataProvider
     */
    public function testGetElementReturnsEmptyElementIfNotExistingElementIsRequested(
        $path,
        $expectedType,
        $expectedId,
        $expectedPath
    ) {
        $expectedConfig = ['id' => $expectedId, 'path' => $expectedPath, '_elementType' => $expectedType];
        $elementMock = $this->getMock(\Magento\Config\Model\Config\Structure\ElementInterface::class);
        $elementMock->expects($this->once())->method('setData')->with($expectedConfig);
        $this->_flyweightFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedType
        )->will(
            $this->returnValue($elementMock)
        );
        $this->assertEquals($elementMock, $this->_model->getElement($path));
    }

    public function emptyElementDataProvider()
    {
        return [
            ['someSection/group_1/nonexisting_field', 'field', 'nonexisting_field', 'someSection/group_1'],
            ['section_1/group_1/nonexisting_field', 'field', 'nonexisting_field', 'section_1/group_1'],
            ['section_1/nonexisting_group', 'group', 'nonexisting_group', 'section_1'],
            ['nonexisting_section', 'section', 'nonexisting_section', '']
        ];
    }

    public function testGetElementReturnsProperElementByPath()
    {
        $elementMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Field::class,
            [],
            [],
            '',
            false
        );
        $section = $this->_structureData['config']['system']['sections']['section_1'];
        $fieldData = $section['children']['group_level_1']['children']['field_3'];
        $elementMock->expects($this->once())->method('setData')->with($fieldData, 'scope');

        $this->_flyweightFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'field'
        )->will(
            $this->returnValue($elementMock)
        );
        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
    }

    public function testGetElementByPathPartsIfSectionDataIsEmpty()
    {
        $elementMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Field::class,
            [],
            [],
            '',
            false
        );
        $fieldData = [
            'id' => 'field_3',
            'path' => 'section_1/group_level_1',
            '_elementType' => 'field',
        ];
        $elementMock->expects($this->once())->method('setData')->with($fieldData, 'scope');

        $this->_flyweightFactory->expects(
            $this->once()
        )->method(
                'create'
            )->with(
                'field'
            )->will(
                $this->returnValue($elementMock)
            );

        $structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );

        $structureDataMock->expects(
            $this->once()
        )->method(
                'get'
            )->will(
                $this->returnValue([])
            );

        $structureMock = new \Magento\Config\Model\Config\Structure(
            $structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );

        $pathParts = explode('/', 'section_1/group_level_1/field_3');
        $this->assertEquals($elementMock, $structureMock->getElementByPathParts($pathParts));
    }

    public function testGetFirstSectionReturnsFirstAllowedSection()
    {
        $tabMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Tab::class,
            ['current', 'getChildren', 'rewind'],
            [],
            '',
            false
        );
        $tabMock->expects($this->any())->method('getChildren')->will($this->returnSelf());
        $tabMock->expects($this->once())->method('rewind');
        $tabMock->expects($this->once())->method('current')->will($this->returnValue('currentSection'));
        $this->_tabIteratorMock->expects($this->once())->method('rewind');
        $this->_tabIteratorMock->expects($this->once())->method('current')->will($this->returnValue($tabMock));
        $this->assertEquals('currentSection', $this->_model->getFirstSection());
    }

    public function testGetElementReturnsProperElementByPathCachesObject()
    {
        $elementMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Field::class,
            [],
            [],
            '',
            false
        );
        $section = $this->_structureData['config']['system']['sections']['section_1'];
        $fieldData = $section['children']['group_level_1']['children']['field_3'];
        $elementMock->expects($this->once())->method('setData')->with($fieldData, 'scope');

        $this->_flyweightFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'field'
        )->will(
            $this->returnValue($elementMock)
        );
        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
    }

    /**
     * @param $attributeName
     * @param $attributeValue
     * @param $paths
     * @dataProvider getFieldPathsByAttributeDataProvider
     */
    public function testGetFieldPathsByAttribute($attributeName, $attributeValue, $paths)
    {
        $this->assertEquals($paths, $this->_model->getFieldPathsByAttribute($attributeName, $attributeValue));
    }

    public function getFieldPathsByAttributeDataProvider()
    {
        return [
            ['backend_model', \Magento\Config\Model\Config\Backend\Encrypted::class, [
                'section_1/group_1/field_2',
                'section_1/group_level_1/group_level_2/group_level_3/field_3_1_1',
                'section_2/group_3/field_4',
            ]],
            ['attribute_2', 'test_value_2', ['section_2/group_3/field_4']]
        ];
    }
}
