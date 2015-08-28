<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout;

/**
 * Test class for \Magento\Framework\View\Layout\ScheduledStructure
 */
class ScheduledStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure
     */
    protected $model;

    /**
     * @var array
     */
    protected $scheduledData = [];

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
        $this->model = new \Magento\Framework\View\Layout\ScheduledStructure($this->scheduledData);
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getListToMove
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
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getListToRemove
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
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getElements
     */
    public function testGetElements()
    {
        $this->assertEquals($this->scheduledData['scheduledElements'], $this->model->getElements());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getElement
     */
    public function testGetElement()
    {
        $expected = $this->scheduledData['scheduledElements']['element2'];
        $this->assertEquals($expected, $this->model->getElement('element2'));

        $default = ['some', 'default', 'value'];
        $this->assertEquals($default, $this->model->getElement('not_existing_element', $default));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::isElementsEmpty
     */
    public function testIsElementsEmpty()
    {
        $this->assertFalse($this->model->isElementsEmpty());
        $this->model->flushScheduledStructure();
        $this->assertTrue($this->model->isElementsEmpty());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setElement
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
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::hasElement
     */
    public function testHasElement()
    {
        $this->assertFalse($this->model->hasElement('not_existing_element'));
        $this->assertTrue($this->model->hasElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetElement
     */
    public function testUnsetElement()
    {
        $this->assertTrue($this->model->hasElement('element1'));
        $this->model->unsetElement('element1');
        $this->assertFalse($this->model->hasElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getElementToMove
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
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setElementToMove
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
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetElementFromListToRemove
     */
    public function testUnsetElementFromListToRemove()
    {
        $this->assertContains('element2', $this->model->getListToRemove());
        $this->model->unsetElementFromListToRemove('element2');
        $this->assertNotContains('element2', $this->model->getListToRemove());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setElementToRemoveList
     */
    public function testSetElementToRemoveList()
    {
        $this->assertNotContains('element1', $this->model->getListToRemove());
        $this->model->setElementToRemoveList('element1');
        $this->assertContains('element1', $this->model->getListToRemove());
    }

    public function testUnsetElementFromIfconfigList()
    {
        $this->assertContains('element4', $this->model->getIfconfigList());
        $this->model->unsetElementFromIfconfigList('element4');
        $this->assertNotContains('element4', $this->model->getIfconfigList());
    }

    public function testSetElementToIfconfigList()
    {
        $this->assertNotContains('element5', $this->model->getIfconfigList());
        $this->model->setElementToIfconfigList('element5', 'config_path', 'scope');
        $this->assertContains('element5', $this->model->getIfconfigList());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getStructure
     */
    public function testGetStructure()
    {
        $this->assertEquals($this->scheduledData['scheduledStructure'], $this->model->getStructure());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getStructureElement
     */
    public function testGetStructureElement()
    {
        $expected = $this->scheduledData['scheduledStructure']['element2'];
        $this->assertEquals($expected, $this->model->getStructureElement('element2'));

        $default = ['some', 'default', 'value'];
        $this->assertEquals($default, $this->model->getStructureElement('not_existing_element', $default));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::isStructureEmpty
     */
    public function testIsStructureEmpty()
    {
        $this->assertFalse($this->model->isStructureEmpty());
        $this->model->flushScheduledStructure();
        $this->assertTrue($this->model->isStructureEmpty());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::hasStructureElement
     */
    public function testHasStructureElement()
    {
        $this->assertTrue($this->model->hasStructureElement('element1'));
        $this->assertFalse($this->model->hasStructureElement('not_existing_element'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setStructureElement
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
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetStructureElement
     */
    public function testUnsetStructureElement()
    {
        $this->assertTrue($this->model->hasStructureElement('element1'));
        $this->model->unsetStructureElement('element1');
        $this->assertFalse($this->model->hasStructureElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getPaths
     */
    public function testGetPaths()
    {
        $this->assertEquals($this->scheduledData['scheduledPaths'], $this->model->getPaths());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getPath
     */
    public function testGetPath()
    {
        $this->assertEquals($this->scheduledData['scheduledPaths']['path1'], $this->model->getPath('path1'));
        $default = ['some', 'data'];
        $this->assertEquals($default, $this->model->getPath('not_existing_element', $default));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::hasPath
     */
    public function testHasPath()
    {
        $this->assertTrue($this->model->hasPath('path1'));
        $this->assertFalse($this->model->hasPath('not_existing_element'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setPathElement
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
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetPathElement
     */
    public function testUnsetPathElement()
    {
        $this->assertTrue($this->model->hasPath('path1'));
        $this->model->unsetPathElement('path1');
        $this->assertFalse($this->model->hasPath('path1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::flushPaths
     */
    public function testFlushPaths()
    {
        $this->assertNotEmpty($this->model->getPaths());
        $this->model->flushPaths();
        $this->assertEmpty($this->model->getPaths());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::flushScheduledStructure
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
     * covers \Magento\Framework\View\Layout\ScheduledStructure::setElementToBrokenParentList
     * covers \Magento\Framework\View\Layout\ScheduledStructure::unsetElementFromBrokenParentList
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
}
