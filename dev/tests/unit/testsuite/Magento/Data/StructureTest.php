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
 * @category    Magento
 * @package     unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Data_StructureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Data_Structure
     */
    protected $_structure;

    protected function setUp()
    {
        $this->_structure = new Magento_Data_Structure;
    }

    /**
     * @param array $elements
     * @dataProvider importExportElementsDataProvider
     */
    public function testConstructImportExportElements($elements)
    {
        $this->assertSame(array(), $this->_structure->exportElements());
        $this->_structure->importElements($elements);
        $this->assertSame($elements, $this->_structure->exportElements());
        $structure = new Magento_Data_Structure($elements);
        $this->assertSame($elements, $structure->exportElements());
    }

    /**
     * @return array
     */
    public function importExportElementsDataProvider()
    {
        return array(
            array(array()),
            array(array('element' => array('arbitrary_key' => 'value'))),
            array(array(
                'one' => array(Magento_Data_Structure::CHILDREN => array('two' => 2, 'three' => 3)),
                'two' => array(Magento_Data_Structure::PARENT => 'one'),
                'three' => array(Magento_Data_Structure::PARENT => 'one'),
                'four' => array(Magento_Data_Structure::CHILDREN => array())
            )),
            array(array(
                'one' => array(
                    Magento_Data_Structure::CHILDREN => array('two' => 't.w.o.'),
                    Magento_Data_Structure::GROUPS => array('group' => array('two' => 'two', 'three' => 'three')),
                ),
                'two' => array(Magento_Data_Structure::PARENT => 'one'),
                'three' => array()
            )),
        );
    }

    /**
     * @param array $elements
     * @dataProvider importExceptionDataProvider
     * @expectedException Magento_Exception
     */
    public function testImportException($elements)
    {
        $this->_structure->importElements($elements);
    }

    public function importExceptionDataProvider()
    {
        return array(
            'numeric id' => array(array('element')),
            'non-existing parent' => array(array('element' => array(Magento_Data_Structure::PARENT => 'unknown'))),
            'completely missing nested set' => array(array(
                'one' => array(Magento_Data_Structure::PARENT => 'two'),
                'two' => array(),
            )),
            'messed up nested set' => array(array(
                'one' => array(Magento_Data_Structure::PARENT => 'two'),
                'two' => array(Magento_Data_Structure::CHILDREN => array('three' => 't.h.r.e.e.')),
                'three' => array(),
            )),
            'nested set invalid data type' => array(array(
                'one' => array(Magento_Data_Structure::CHILDREN => '')
            )),
            'duplicate aliases' => array(array(
                'one' => array(Magento_Data_Structure::CHILDREN => array('two' => 'alias', 'three' => 'alias')),
                'two' => array(Magento_Data_Structure::PARENT => 'one'),
                'three' => array(Magento_Data_Structure::PARENT => 'one'),
            )),
            'missing child' => array(array(
                'one' => array(Magento_Data_Structure::CHILDREN => array('two' => 't.w.o.', 'three' => 't.h.r.e.e.')),
                'two' => array(Magento_Data_Structure::PARENT => 'one'),
            )),
            'missing reference back to parent' => array(array(
                'one' => array(Magento_Data_Structure::CHILDREN => array('two' => 't.w.o.')),
                'two' => array(),
            )),
            'broken reference back to parent' => array(array(
                'one' => array(Magento_Data_Structure::CHILDREN => array('two' => 't.w.o.', 'three' => 't.h.r.e.e.')),
                'two' => array(Magento_Data_Structure::PARENT => 'three'),
                'three' => array(Magento_Data_Structure::PARENT => 'one')
            )),
            'groups invalid data type' => array(array(
                'one' => array(Magento_Data_Structure::GROUPS => '')
            )),
            'group invalid data type' => array(array(
                'one' => array(Magento_Data_Structure::GROUPS => array(1))
            )),
            'asymmetric group' => array(array(
                'one' => array(Magento_Data_Structure::GROUPS => array('two' => 'three')),
                'two' => array(),
                'three' => array(),
            )),
            'group references to non-existing element' => array(array(
                'one' => array(Magento_Data_Structure::GROUPS => array('two' => 'two')),
            )),
        );
    }

    public function testCreateGetHasElement()
    {
        $data = array(uniqid() => uniqid());
        $elementId = uniqid('id');
        $this->assertFalse($this->_structure->hasElement($elementId));
        $this->assertFalse($this->_structure->getElement($elementId));

        $this->_structure->createElement($elementId, $data);
        $this->assertTrue($this->_structure->hasElement($elementId));
        $this->assertSame($data, $this->_structure->getElement($elementId));
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testCreateElementException()
    {
        $elementId = uniqid('id');
        $this->_structure->createElement($elementId, array());
        $this->_structure->createElement($elementId, array());
    }

    public function testUnsetElement()
    {
        $this->_populateSampleStructure();

        // non-recursively
        $this->assertTrue($this->_structure->unsetElement('six', false));
        $this->assertFalse($this->_structure->unsetElement('six', false));
        $this->assertSame(array(5), $this->_structure->getElement('five'));

        // recursively
        $this->assertTrue($this->_structure->unsetElement('four'));
        $this->assertSame(array('one' => array(), 'five' => array(5)), $this->_structure->exportElements());
    }

    public function testSetGetAttribute()
    {
        $this->_populateSampleStructure();
        $this->assertFalse($this->_structure->getAttribute('two', 'non-existing'));
        $this->assertEquals('bar', $this->_structure->getAttribute('two', 'foo'));
        $value = uniqid();
        $this->_structure->setAttribute('two', 'non-existing', $value)
            ->setAttribute('two', 'foo', $value)
        ;
        $this->assertEquals($value, $this->_structure->getAttribute('two', 'non-existing'));
        $this->assertEquals($value, $this->_structure->getAttribute('two', 'foo'));
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testSetAttributeNoElementException()
    {
        $this->_structure->setAttribute('non-existing', 'foo', 'bar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider setAttributeArgumentExceptionDataProvider
     * @param string $attribute
     */
    public function testSetAttributeArgumentException($attribute)
    {
        $this->_structure->importElements(array('element' => array()));
        $this->_structure->setAttribute('element', $attribute, 'value');
    }

    /**
     * @return array
     */
    public function setAttributeArgumentExceptionDataProvider()
    {
        return array(
            array(Magento_Data_Structure::CHILDREN),
            array(Magento_Data_Structure::PARENT),
            array(Magento_Data_Structure::GROUPS),
        );
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetAttributeNoElementException()
    {
        $this->_structure->getAttribute('non-existing', 'foo');
    }

    public function testRenameElement()
    {
        $this->_populateSampleStructure();

        // rename element and see how children got updated
        $element = $this->_structure->getElement('four');
        $this->assertNotEmpty($element);
        $this->assertFalse($this->_structure->getElement('four.5'));
        $this->assertSame($this->_structure, $this->_structure->renameElement('four', 'four.5'));
        $this->assertSame($element, $this->_structure->getElement('four.5'));
        $this->assertEquals('four.5', $this->_structure->getAttribute('two', Magento_Data_Structure::PARENT));
        $this->assertEquals('four.5', $this->_structure->getAttribute('three', Magento_Data_Structure::PARENT));

        // rename element and see how parent got updated
        $this->_structure->renameElement('three', 'three.5'); // first child
        $this->assertSame(array('three.5' => 'th', 'two' => 'tw'), $this->_structure->getChildren('four.5'));
        $this->_structure->renameElement('two', 'two.5'); // second and last child
        $this->assertSame(array('three.5' => 'th', 'two.5' => 'tw'), $this->_structure->getChildren('four.5'));
    }

    public function testSetAsChild()
    {
        $this->_populateSampleStructure();

        // default alias
        $this->_structure->setAsChild('two', 'one');
        $this->assertEquals('one', $this->_structure->getParentId('two'));
        $this->assertEquals(array('two' => 'two'), $this->_structure->getChildren('one'));
        $this->assertEquals(array('three' => 'th'), $this->_structure->getChildren('four'));

        // specified alias
        $this->_structure->setAsChild('six', 'three', 's');
        $this->assertEquals('three', $this->_structure->getParentId('six'));
        $this->assertEquals(array('six' => 's'), $this->_structure->getChildren('three'));
    }

    /**
     * @param int $offset
     * @param int $expectedOffset
     * @dataProvider setAsChildOffsetDataProvider
     */
    public function testSetAsChildOffset($offset, $expectedOffset)
    {
        $this->_populateSampleSortStructure();
        $this->_structure->setAsChild('x', 'parent', '', $offset);
        $children = $this->_structure->getChildren('parent');
        $actualOffset = array_search('x', array_keys($children));
        $this->assertSame($expectedOffset, $actualOffset,
            "The 'x' is expected to be at '{$expectedOffset}' offset, rather than '{$actualOffset}', in array: "
                . var_export($children, 1)
        );
    }

    /**
     * @return array
     */
    public function setAsChildOffsetDataProvider()
    {
        return array(
            array(0, 0), array(1, 1), array(2, 2), array(3, 3), array(4, 4), array(5, 5),
            array(null, 5),
            array(-1, 4), array(-2, 3), array(-3, 2), array(-4, 1), array(-5, 0),
        );
    }

    /**
     * @param string $elementId
     * @param string $parentId
     * @expectedException Magento_Exception
     * @dataProvider setAsChildExceptionDataProvider
     */
    public function testSetAsChildException($elementId, $parentId)
    {
        $this->_structure->createElement('one', array());
        $this->_structure->createElement('two', array());
        $this->_structure->createElement('three', array());
        $this->_structure->setAsChild('three', 'two');
        $this->_structure->setAsChild('two', 'one');
        $this->_structure->setAsChild($elementId, $parentId);
    }

    /**
     * @return array
     */
    public function setAsChildExceptionDataProvider()
    {
        return array(
            array('one', 'three'),
            array('one', 'one'),
        );
    }

    public function testUnsetChild()
    {
        $this->_populateSampleStructure();

        // specify element by name
        $this->_structure->unsetChild('five');
        $this->assertFalse($this->_structure->getParentId('five'));
        $this->assertArrayNotHasKey(Magento_Data_Structure::CHILDREN, $this->_structure->getElement('six'));

        // specify element by parent and alias
        $this->_structure->unsetChild('four', 'tw');
        $this->assertFalse($this->_structure->getChildId('four', 'tw'));
        $this->assertFalse($this->_structure->getParentId('two'));
    }

    /**
     * @param int $initialOffset
     * @param int $newOffset
     * @param int $expectedOffset
     * @dataProvider reorderChildDataProvider
     */
    public function testReorderChild($initialOffset, $newOffset, $expectedOffset)
    {
        $this->_populateSampleSortStructure();
        $this->_structure->setAsChild('x', 'parent', '', $initialOffset);
        $this->assertSame($expectedOffset, $this->_structure->reorderChild('parent', 'x', $newOffset));
    }

    /**
     * @return array
     */
    public function reorderChildDataProvider()
    {
        return array(
            // x* 1 2 3 4 5
            array(0, 0, 1), array(0, 1, 1), array(0, 2, 2), array(0, 3, 3), array(0, +100500, 6),
            array(0, -1, 5), array(0, -4, 2), array(0, -5, 1), array(0, -999, 1),
            // 1 x* 2 3 4 5
            array(1, 0, 1), array(1, 1, 2), array(1, 2, 2), array(1, 3, 3),
            array(1, -1, 5), array(1, -4, 2), array(1, -5, 2), array(1, -6, 1),
            // 1 2 x* 3 4 5
            array(2, 0, 1), array(2, 1, 2), array(2, 2, 3), array(2, 3, 3), array(2, 4, 4), array(2, null, 6),
            // 1 2 3 4 5 x*
            array(5, 0, 1), array(5, 1, 2), array(5, 5, 6)
        );
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testReorderChildException()
    {
        $this->_structure->createElement('one', array());
        $this->_structure->createElement('two', array());
        $this->_structure->reorderChild('one', 'two', 0);
    }

    /**
     * @param int $initialOffset
     * @param string $sibling
     * @param int $delta
     * @param int $expectedOffset
     * @dataProvider reorderSiblingDataProvider
     */
    public function testReorderToSibling($initialOffset, $sibling, $delta, $expectedOffset)
    {
        $this->_populateSampleSortStructure();
        $this->_structure->setAsChild('x', 'parent', '', $initialOffset);
        $this->assertSame($expectedOffset, $this->_structure->reorderToSibling('parent', 'x', $sibling, $delta));
    }

    public function reorderSiblingDataProvider()
    {
        return array(
            // x* 1 2 3 4 5
            array(0, 'one', 1, 2), array(0, 'three', 2, 5), array(0, 'five', 1, 6), array(0, 'five', 10, 6),
            array(0, 'one', -1, 1), array(0, 'one', -999, 1),
            // 1 2 x* 3 4 5
            array(2, 'two', 1, 3), array(2, 'two', 2, 4), array(2, 'two', 3, 5),  array(2, 'two', 999, 6),
            array(2, 'two', -1, 2), array(2, 'two', -2, 1), array(2, 'two', -999, 1),
            array(2, 'x', 1, 3), array(2, 'x', 2, 4), array(2, 'x', 3, 5),  array(2, 'x', 999, 6),
            array(2, 'x', -1, 3), array(2, 'x', -2, 2), array(2, 'x', -999, 1),
        );
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testReorderToSiblingException()
    {
        $this->_structure->createElement('one', array());
        $this->_structure->createElement('two', array());
        $this->_structure->createElement('three', array());
        $this->_structure->setAsChild('two', 'one');
        $this->_structure->reorderToSibling('one', 'three', 'two', 1);
    }

    public function testGetChildId()
    {
        $this->_populateSampleStructure();
        $this->assertFalse($this->_structure->getChildId('nonexisting-parent', 'does not matter'));
        $this->assertEquals('five', $this->_structure->getChildId('six', 'f'));
    }

    public function testGetChildrenParentIdChildAlias()
    {
        $this->_structure->createElement('one', array());
        $this->_structure->createElement('two', array());
        $this->_structure->createElement('three', array());
        $this->_structure->setAsChild('two', 'one');
        $this->_structure->setAsChild('three', 'one', 'th');

        // getChildren()
        $this->assertSame(array('two' => 'two', 'three' => 'th'), $this->_structure->getChildren('one'));
        $this->assertSame(array(), $this->_structure->getChildren('three'));
        $this->assertSame(array(), $this->_structure->getChildren('nonexisting'));

        // getParentId()
        $this->assertEquals('one', $this->_structure->getParentId('two'));
        $this->assertFalse($this->_structure->getParentId('nonexistent'));

        // getChildAlias()
        $this->assertEquals('two', $this->_structure->getChildAlias('one', 'two'));
        $this->assertEquals('th', $this->_structure->getChildAlias('one', 'three'));
        $this->assertFalse($this->_structure->getChildAlias('nonexistent', 'child'));
        $this->assertFalse($this->_structure->getChildAlias('one', 'nonexistent'));
    }

    /**
     * @covers Magento_Data_Structure::addToParentGroup
     * @covers Magento_Data_Structure::getGroupChildNames
     */
    public function testGroups()
    {
        // non-existing element
        $this->assertFalse($this->_structure->addToParentGroup('non-existing', 'group1'));
        $this->assertSame(array(), $this->_structure->getGroupChildNames('non-existing', 'group1'));

        // not a child
        $this->_structure->createElement('one', array());
        $this->_structure->createElement('two', array());
        $this->assertFalse($this->_structure->addToParentGroup('two', 'group1'));
        $this->assertSame(array(), $this->_structure->getGroupChildNames('one', 'group1'));

        // child
        $this->_structure->setAsChild('two', 'one');
        $this->assertTrue($this->_structure->addToParentGroup('two', 'group1'));
        $this->assertTrue($this->_structure->addToParentGroup('two', 'group2'));

        // group getter
        $this->_structure->createElement('three', array());
        $this->_structure->createElement('four', array());
        $this->_structure->setAsChild('three', 'one', 'th');
        $this->_structure->setAsChild('four', 'one');
        $this->_structure->addToParentGroup('three', 'group1');
        $this->_structure->addToParentGroup('four', 'group2');
        $this->assertSame(array('two', 'three'), $this->_structure->getGroupChildNames('one', 'group1'));
        $this->assertSame(array('two', 'four'), $this->_structure->getGroupChildNames('one', 'group2'));

        // unset a child
        $this->_structure->unsetChild('one', 'two');
        $this->assertSame(array('three'), $this->_structure->getGroupChildNames('one', 'group1'));
        $this->assertSame(array('four'), $this->_structure->getGroupChildNames('one', 'group2'));

        // return child back
        $this->_structure->setAsChild('two', 'one');
        $this->assertSame(array('two', 'three'), $this->_structure->getGroupChildNames('one', 'group1'));
        $this->assertSame(array('two', 'four'), $this->_structure->getGroupChildNames('one', 'group2'));
    }

    /**
     * Import a sample valid structure
     */
    protected function _populateSampleStructure()
    {
        $this->_structure->importElements(array(
            'one' => array(),
            'two' => array(Magento_Data_Structure::PARENT => 'four', 'foo' => 'bar'),
            'three' => array(Magento_Data_Structure::PARENT => 'four', 'bar' => 'baz'),
            'four' => array(Magento_Data_Structure::CHILDREN => array('three' => 'th', 'two' => 'tw')),
            'five' => array(Magento_Data_Structure::PARENT => 'six', 5),
            'six' => array(Magento_Data_Structure::CHILDREN => array('five' => 'f')),
        ));
    }

    /**
     * Import a sample structure, suitable for testing elements sort order
     */
    protected function _populateSampleSortStructure()
    {
        $child = array(Magento_Data_Structure::PARENT => 'parent');
        $this->_structure->importElements(array(
            'parent' => array(Magento_Data_Structure::CHILDREN => array(
                'one' => 'e1', 'two' => 'e2', 'three' => 'e3', 'four' => 'e4', 'five' => 'e5',
            )),
            'one' => $child, 'two' => $child, 'three' => $child, 'four' => $child, 'five' => $child,
            'x' => array(),
        ));
    }
}
