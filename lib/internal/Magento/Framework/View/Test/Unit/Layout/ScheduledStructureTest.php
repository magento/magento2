<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout;

use \Magento\Framework\View\Layout\ScheduledStructure;

/**
 * Test class for \Magento\Framework\View\Layout\ScheduledStructure
 */
class ScheduledStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScheduledStructure
     */
    protected $model;

    /**
     * @var array
     */
    protected $scheduledData = [];

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->scheduledData = [
            'scheduledStructure' => [
                'element1' => ['data', 'of', 'element', '1'],
                'element2' => ['data', 'of', 'element', '2'],
                'element3' => ['data', 'of', 'element', '3'],
                'element4' => ['data', 'of', 'element', '4'],
                'element5' => ['data', 'of', 'element', '5'],
            ],
            'scheduledElements' => [
                'element1' => ['data', 'of', 'element', '1'],
                'element2' => ['data', 'of', 'element', '2'],
                'element3' => ['data', 'of', 'element', '3'],
                'element4' => ['data', 'of', 'element', '4'],
                'element5' => ['data', 'of', 'element', '5'],
                'element9' => ['data', 'of', 'element', '9'],
            ],
            'scheduledMoves' => [
                'element1' => ['data', 'of', 'element', 'to', 'move', '1'],
                'element4' => ['data', 'of', 'element', 'to', 'move', '4'],
                'element6' => ['data', 'of', 'element', 'to', 'move', '6'],
            ],
            'scheduledRemoves' => [
                'element2' => ['data', 'of', 'element', 'to', 'remove', '2'],
                'element3' => ['data', 'of', 'element', 'to', 'remove', '3'],
                'element6' => ['data', 'of', 'element', 'to', 'remove', '6'],
                'element7' => ['data', 'of', 'element', 'to', 'remove', '7'],
            ],
            'scheduledIfconfig' => [
                'element1' => ['data', 'of', 'ifconfig', 'element', '1'],
                'element4' => ['data', 'of', 'ifconfig', 'element', '4'],
                'element6' => ['data', 'of', 'ifconfig', 'element', '6'],
                'element8' => ['data', 'of', 'ifconfig', 'element', '8'],
            ],
            'scheduledPaths' => [
                'path1' => 'path 1',
                'path2' => 'path 2',
                'path3' => 'path 3',
                'path4' => 'path 4',
            ],
        ];

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helperObjectManager->getObject(
            'Magento\Framework\View\Layout\ScheduledStructure',
            ['data' => $this->scheduledData]
        );
    }

    /**
     * @return void
     */
    public function testGetListToMove()
    {
        /**
         * Only elements that are present in elements list and specified in list to move can be moved
         */
        $expected = ['element1', 'element4'];
        $this->assertEquals($expected, $this->model->getListToMove());
    }

    /**
     * @return void
     */
    public function testGetListToRemove()
    {
        /**
         * Only elements that are present in elements list and specified in list to remove can be removed
         */
        $expected = ['element2', 'element3'];
        $this->assertEquals($expected, $this->model->getListToRemove());
    }

    public function testGetIfconfigList()
    {
        $expected = ['element1', 'element4'];
        $this->assertEquals($expected, $this->model->getIfconfigList());
    }

    /**
     * @return void
     */
    public function testGetElements()
    {
        $this->assertEquals($this->scheduledData['scheduledElements'], $this->model->getElements());
    }

    /**
     * @return void
     */
    public function testGetElement()
    {
        $expected = $this->scheduledData['scheduledElements']['element2'];
        $this->assertEquals($expected, $this->model->getElement('element2'));

        $default = ['some', 'default', 'value'];
        $this->assertEquals($default, $this->model->getElement('not_existing_element', $default));
    }

    /**
     * @return void
     */
    public function testIsElementsEmpty()
    {
        $this->assertFalse($this->model->isElementsEmpty());
        $this->model->flushScheduledStructure();
        $this->assertTrue($this->model->isElementsEmpty());
    }

    /**
     * @return void
     */
    public function testSetElement()
    {
        $data = ['some', 'new', 'data'];

        /** Test add new element */
        $this->assertFalse($this->model->hasElement('new_element'));
        $this->model->setElement('new_element', $data);
        $this->assertEquals($data, $this->model->getElement('new_element'));

        /** Test override existing element */
        $this->assertTrue($this->model->hasElement('element1'));
        $this->model->setElement('element1', $data);
        $this->assertEquals($data, $this->model->getElement('element1'));
    }

    /**
     * @return void
     */
    public function testHasElement()
    {
        $this->assertFalse($this->model->hasElement('not_existing_element'));
        $this->assertTrue($this->model->hasElement('element1'));
    }

    /**
     * @return void
     */
    public function testUnsetElement()
    {
        $this->assertTrue($this->model->hasElement('element1'));
        $this->model->unsetElement('element1');
        $this->assertFalse($this->model->hasElement('element1'));
    }

    /**
     * @return void
     */
    public function testGetElementToMove()
    {
        $this->assertEquals(
            $this->scheduledData['scheduledMoves']['element1'],
            $this->model->getElementToMove('element1')
        );
        $default = ['some', 'data'];
        $this->assertEquals($default, $this->model->getElementToMove('not_existing_element', $default));
    }

    /**
     * @return void
     */
    public function getIfconfigElement()
    {
        $this->assertEquals(
            $this->scheduledData['scheduledIfconfig']['element1'],
            $this->model->getIfconfigElement('element1')
        );
        $default = ['some', 'data'];
        $this->assertEquals($default, $this->model->getIfconfigElement('not_existing_element', $default));
    }

    /**
     * @return void
     */
    public function testSetElementToMove()
    {
        $data = ['some', 'new', 'data', 'element', 'to', 'move'];

        /** Test add new element */
        $this->assertFalse($this->model->hasElement('new_element'));
        $this->model->setElementToMove('new_element', $data);
        $this->assertEquals($data, $this->model->getElementToMove('new_element'));

        /** Test override existing element */
        $this->assertNotEquals($data, $this->model->getElementToMove('element1'));
        $this->model->setElementToMove('element1', $data);
        $this->assertEquals($data, $this->model->getElementToMove('element1'));
    }

    /**
     * @return void
     */
    public function testUnsetElementFromListToRemove()
    {
        $this->assertContains('element2', $this->model->getListToRemove());
        $this->model->unsetElementFromListToRemove('element2');
        $this->assertNotContains('element2', $this->model->getListToRemove());
    }

    /**
     * @return void
     */
    public function testSetElementToRemoveList()
    {
        $this->assertNotContains('element1', $this->model->getListToRemove());
        $this->model->setElementToRemoveList('element1');
        $this->assertContains('element1', $this->model->getListToRemove());
    }

    /**
     * @return void
     */
    public function testUnsetElementFromIfconfigList()
    {
        $this->assertContains('element4', $this->model->getIfconfigList());
        $this->model->unsetElementFromIfconfigList('element4');
        $this->assertNotContains('element4', $this->model->getIfconfigList());
    }

    /**
     * @return void
     */
    public function testSetElementToIfconfigList()
    {
        $this->assertNotContains('element5', $this->model->getIfconfigList());
        $this->model->setElementToIfconfigList('element5', 'config_path', 'scope');
        $this->assertContains('element5', $this->model->getIfconfigList());
    }

    /**
     * @return void
     */
    public function testGetStructure()
    {
        $this->assertEquals($this->scheduledData['scheduledStructure'], $this->model->getStructure());
    }

    /**
     * @return void
     */
    public function testGetStructureElement()
    {
        $expected = $this->scheduledData['scheduledStructure']['element2'];
        $this->assertEquals($expected, $this->model->getStructureElement('element2'));

        $default = ['some', 'default', 'value'];
        $this->assertEquals($default, $this->model->getStructureElement('not_existing_element', $default));
    }

    /**
     * @return void
     */
    public function testIsStructureEmpty()
    {
        $this->assertFalse($this->model->isStructureEmpty());
        $this->model->flushScheduledStructure();
        $this->assertTrue($this->model->isStructureEmpty());
    }

    /**
     * @return void
     */
    public function testHasStructureElement()
    {
        $this->assertTrue($this->model->hasStructureElement('element1'));
        $this->assertFalse($this->model->hasStructureElement('not_existing_element'));
    }

    /**
     * @return void
     */
    public function testSetStructureElement()
    {
        $data = ['some', 'new', 'data', 'structure', 'element'];

        /** Test add new structure element */
        $this->assertFalse($this->model->hasStructureElement('new_element'));
        $this->model->setStructureElement('new_element', $data);
        $this->assertEquals($data, $this->model->getStructureElement('new_element'));

        /** Test override existing structure element */
        $this->assertTrue($this->model->hasStructureElement('element1'));
        $this->model->setStructureElement('element1', $data);
        $this->assertEquals($data, $this->model->getStructureElement('element1'));
    }

    /**
     * @return void
     */
    public function testUnsetStructureElement()
    {
        $this->assertTrue($this->model->hasStructureElement('element1'));
        $this->model->unsetStructureElement('element1');
        $this->assertFalse($this->model->hasStructureElement('element1'));
    }

    /**
     * @return void
     */
    public function testGetPaths()
    {
        $this->assertEquals($this->scheduledData['scheduledPaths'], $this->model->getPaths());
    }

    /**
     * @return void
     */
    public function testGetPath()
    {
        $this->assertEquals($this->scheduledData['scheduledPaths']['path1'], $this->model->getPath('path1'));
        $default = ['some', 'data'];
        $this->assertEquals($default, $this->model->getPath('not_existing_element', $default));
    }

    /**
     * @return void
     */
    public function testHasPath()
    {
        $this->assertTrue($this->model->hasPath('path1'));
        $this->assertFalse($this->model->hasPath('not_existing_element'));
    }

    /**
     * @return void
     */
    public function testSetPathElement()
    {
        $data = ['some', 'new', 'data', 'path'];

        /** Test add new structure element */
        $this->assertFalse($this->model->hasPath('new_element'));
        $this->model->setPathElement('new_element', $data);
        $this->assertEquals($data, $this->model->getPath('new_element'));

        /** Test override existing structure element */
        $this->assertTrue($this->model->hasPath('path1'));
        $this->model->setPathElement('path1', $data);
        $this->assertEquals($data, $this->model->getPath('path1'));
    }

    /**
     * @return void
     */
    public function testUnsetPathElement()
    {
        $this->assertTrue($this->model->hasPath('path1'));
        $this->model->unsetPathElement('path1');
        $this->assertFalse($this->model->hasPath('path1'));
    }

    /**
     * @return void
     */
    public function testFlushPaths()
    {
        $this->assertNotEmpty($this->model->getPaths());
        $this->model->flushPaths();
        $this->assertEmpty($this->model->getPaths());
    }

    /**
     * @return void
     */
    public function testFlushScheduledStructure()
    {
        $this->assertNotEmpty($this->model->getPaths());
        $this->assertNotEmpty($this->model->getElements());
        $this->assertNotEmpty($this->model->getStructure());

        $this->model->flushScheduledStructure();

        $this->assertEmpty($this->model->getPaths());
        $this->assertEmpty($this->model->getElements());
        $this->assertEmpty($this->model->getStructure());
    }

    /**
     * @return void
     */
    public function testSetElementToBrokenParentList()
    {
        $element = 'element9';
        $expectedToRemove = ['element2', 'element3'];
        $expectedToRemoveWithBroken = ['element2', 'element3', 'element9'];
        $this->assertEquals($expectedToRemove, $this->model->getListToRemove());

        $this->model->setElementToBrokenParentList($element);
        $this->assertEquals($expectedToRemoveWithBroken, $this->model->getListToRemove());

        $this->model->unsetElementFromBrokenParentList($element);
        $this->assertEquals($expectedToRemove, $this->model->getListToRemove());
    }

    /**
     * @return void
     */
    public function testSetElementToSortList()
    {
        list($parentName, $name, $sibling, $isAfter, $element) = $this->getDataSort();
        $elementsList = [$name => $element];
        $this->assertArrayNotHasKey($name, $this->model->getListToSort());
        $this->model->setElementToSortList($parentName, $name, $sibling, $isAfter);
        $this->assertEquals($elementsList, $this->model->getListToSort());
    }

    /**
     * @return void
     */
    public function testGetElementToSortEmpty()
    {
        $this->assertEmpty($this->model->getElementToSort('test'));
    }

    /**
     * @return void
     */
    public function testGetElementToSort()
    {
        list($parentName, $name, $sibling, $isAfter, $element) = $this->getDataSort();
        $this->model->setElementToSortList($parentName, $name, $sibling, $isAfter);
        $this->assertEquals($element, $this->model->getElementToSort($name));
    }

    /**
     * @return void
     */
    public function testUnsetElementToSort()
    {
        list($parentName, $name, $sibling, $isAfter) = $this->getDataSort();
        $this->model->setElementToSortList($parentName, $name, $sibling, $isAfter);
        $this->assertArrayHasKey($name, $this->model->getListToSort());
        $this->model->unsetElementToSort($name);
        $this->assertArrayNotHasKey($name, $this->model->getListToSort());
    }

    /**
     * @return void
     */
    public function testIsListToSortEmpty()
    {
        list($parentName, $name, $sibling, $isAfter) = $this->getDataSort();
        $this->assertTrue($this->model->isListToSortEmpty());
        $this->model->setElementToSortList($parentName, $name, $sibling, $isAfter);
        $this->assertFalse($this->model->isListToSortEmpty());
    }

    /**
     * @return array
     */
    protected function getDataSort()
    {
        return [
            'parent name',
            'element name',
            'sibling',
            false,
            [
                ScheduledStructure::ELEMENT_NAME => 'element name',
                ScheduledStructure::ELEMENT_PARENT_NAME => 'parent name',
                ScheduledStructure::ELEMENT_OFFSET_OR_SIBLING => 'sibling',
                ScheduledStructure::ELEMENT_IS_AFTER => false
            ]
        ];
    }
}
