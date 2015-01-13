<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Test class for \Magento\Framework\View\Layout\ScheduledStructure
 */
class ScheduledStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_scheduledData = [];

    protected function setUp()
    {
        $this->_scheduledData = [
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
            'scheduledPaths' => [
                'path1' => 'path 1',
                'path2' => 'path 2',
                'path3' => 'path 3',
                'path4' => 'path 4',
            ],
        ];
        $this->_model = new \Magento\Framework\View\Layout\ScheduledStructure($this->_scheduledData);
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
        $this->assertEquals($expected, $this->_model->getListToMove());
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
        $this->assertEquals($expected, $this->_model->getListToRemove());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getElements
     */
    public function testGetElements()
    {
        $this->assertEquals($this->_scheduledData['scheduledElements'], $this->_model->getElements());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getElement
     */
    public function testGetElement()
    {
        $expected = $this->_scheduledData['scheduledElements']['element2'];
        $this->assertEquals($expected, $this->_model->getElement('element2'));

        $default = ['some', 'default', 'value'];
        $this->assertEquals($default, $this->_model->getElement('not_existing_element', $default));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::isElementsEmpty
     */
    public function testIsElementsEmpty()
    {
        $this->assertFalse($this->_model->isElementsEmpty());
        $this->_model->flushScheduledStructure();
        $this->assertTrue($this->_model->isElementsEmpty());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setElement
     */
    public function testSetElement()
    {
        $data = ['some', 'new', 'data'];

        /** Test add new element */
        $this->assertFalse($this->_model->hasElement('new_element'));
        $this->_model->setElement('new_element', $data);
        $this->assertEquals($data, $this->_model->getElement('new_element'));

        /** Test override existing element */
        $this->assertTrue($this->_model->hasElement('element1'));
        $this->_model->setElement('element1', $data);
        $this->assertEquals($data, $this->_model->getElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::hasElement
     */
    public function testHasElement()
    {
        $this->assertFalse($this->_model->hasElement('not_existing_element'));
        $this->assertTrue($this->_model->hasElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetElement
     */
    public function testUnsetElement()
    {
        $this->assertTrue($this->_model->hasElement('element1'));
        $this->_model->unsetElement('element1');
        $this->assertFalse($this->_model->hasElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getElementToMove
     */
    public function testGetElementToMove()
    {
        $this->assertEquals(
            $this->_scheduledData['scheduledMoves']['element1'],
            $this->_model->getElementToMove('element1')
        );
        $default = ['some', 'data'];
        $this->assertEquals($default, $this->_model->getElementToMove('not_existing_element', $default));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setElementToMove
     */
    public function testSetElementToMove()
    {
        $data = ['some', 'new', 'data', 'element', 'to', 'move'];

        /** Test add new element */
        $this->assertFalse($this->_model->hasElement('new_element'));
        $this->_model->setElementToMove('new_element', $data);
        $this->assertEquals($data, $this->_model->getElementToMove('new_element'));

        /** Test override existing element */
        $this->assertNotEquals($data, $this->_model->getElementToMove('element1'));
        $this->_model->setElementToMove('element1', $data);
        $this->assertEquals($data, $this->_model->getElementToMove('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetElementFromListToRemove
     */
    public function testUnsetElementFromListToRemove()
    {
        $this->assertContains('element2', $this->_model->getListToRemove());
        $this->_model->unsetElementFromListToRemove('element2');
        $this->assertNotContains('element2', $this->_model->getListToRemove());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setElementToRemoveList
     */
    public function testSetElementToRemoveList()
    {
        $this->assertNotContains('element1', $this->_model->getListToRemove());
        $this->_model->setElementToRemoveList('element1');
        $this->assertContains('element1', $this->_model->getListToRemove());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getStructure
     */
    public function testGetStructure()
    {
        $this->assertEquals($this->_scheduledData['scheduledStructure'], $this->_model->getStructure());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getStructureElement
     */
    public function testGetStructureElement()
    {
        $expected = $this->_scheduledData['scheduledStructure']['element2'];
        $this->assertEquals($expected, $this->_model->getStructureElement('element2'));

        $default = ['some', 'default', 'value'];
        $this->assertEquals($default, $this->_model->getStructureElement('not_existing_element', $default));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::isStructureEmpty
     */
    public function testIsStructureEmpty()
    {
        $this->assertFalse($this->_model->isStructureEmpty());
        $this->_model->flushScheduledStructure();
        $this->assertTrue($this->_model->isStructureEmpty());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::hasStructureElement
     */
    public function testHasStructureElement()
    {
        $this->assertTrue($this->_model->hasStructureElement('element1'));
        $this->assertFalse($this->_model->hasStructureElement('not_existing_element'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setStructureElement
     */
    public function testSetStructureElement()
    {
        $data = ['some', 'new', 'data', 'structure', 'element'];

        /** Test add new structure element */
        $this->assertFalse($this->_model->hasStructureElement('new_element'));
        $this->_model->setStructureElement('new_element', $data);
        $this->assertEquals($data, $this->_model->getStructureElement('new_element'));

        /** Test override existing structure element */
        $this->assertTrue($this->_model->hasStructureElement('element1'));
        $this->_model->setStructureElement('element1', $data);
        $this->assertEquals($data, $this->_model->getStructureElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetStructureElement
     */
    public function testUnsetStructureElement()
    {
        $this->assertTrue($this->_model->hasStructureElement('element1'));
        $this->_model->unsetStructureElement('element1');
        $this->assertFalse($this->_model->hasStructureElement('element1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getPaths
     */
    public function testGetPaths()
    {
        $this->assertEquals($this->_scheduledData['scheduledPaths'], $this->_model->getPaths());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::getPath
     */
    public function testGetPath()
    {
        $this->assertEquals($this->_scheduledData['scheduledPaths']['path1'], $this->_model->getPath('path1'));
        $default = ['some', 'data'];
        $this->assertEquals($default, $this->_model->getPath('not_existing_element', $default));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::hasPath
     */
    public function testHasPath()
    {
        $this->assertTrue($this->_model->hasPath('path1'));
        $this->assertFalse($this->_model->hasPath('not_existing_element'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::setPathElement
     */
    public function testSetPathElement()
    {
        $data = ['some', 'new', 'data', 'path'];

        /** Test add new structure element */
        $this->assertFalse($this->_model->hasPath('new_element'));
        $this->_model->setPathElement('new_element', $data);
        $this->assertEquals($data, $this->_model->getPath('new_element'));

        /** Test override existing structure element */
        $this->assertTrue($this->_model->hasPath('path1'));
        $this->_model->setPathElement('path1', $data);
        $this->assertEquals($data, $this->_model->getPath('path1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::unsetPathElement
     */
    public function testUnsetPathElement()
    {
        $this->assertTrue($this->_model->hasPath('path1'));
        $this->_model->unsetPathElement('path1');
        $this->assertFalse($this->_model->hasPath('path1'));
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::flushPaths
     */
    public function testFlushPaths()
    {
        $this->assertNotEmpty($this->_model->getPaths());
        $this->_model->flushPaths();
        $this->assertEmpty($this->_model->getPaths());
    }

    /**
     * @covers \Magento\Framework\View\Layout\ScheduledStructure::flushScheduledStructure
     */
    public function testFlushScheduledStructure()
    {
        $this->assertNotEmpty($this->_model->getPaths());
        $this->assertNotEmpty($this->_model->getElements());
        $this->assertNotEmpty($this->_model->getStructure());

        $this->_model->flushScheduledStructure();

        $this->assertEmpty($this->_model->getPaths());
        $this->assertEmpty($this->_model->getElements());
        $this->assertEmpty($this->_model->getStructure());
    }
}
