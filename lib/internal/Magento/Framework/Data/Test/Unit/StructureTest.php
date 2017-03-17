<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

class StructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Structure
     */
    protected $_structure;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->_structure = new \Magento\Framework\Data\Structure();
    }

    /**
     * @param array $elements
     * @return void
     * @dataProvider importExportElementsDataProvider
     */
    public function testConstructImportExportElements($elements)
    {
        $this->assertSame([], $this->_structure->exportElements());
        $this->_structure->importElements($elements);
        $this->assertSame($elements, $this->_structure->exportElements());
        $structure = new \Magento\Framework\Data\Structure($elements);
        $this->assertSame($elements, $structure->exportElements());
    }

    /**
     * @return array
     */
    public function importExportElementsDataProvider()
    {
        return [
            [[]],
            [['element' => ['arbitrary_key' => 'value']]],
            [
                [
                    'one' => [\Magento\Framework\Data\Structure::CHILDREN => ['two' => 2, 'three' => 3]],
                    'two' => [\Magento\Framework\Data\Structure::PARENT => 'one'],
                    'three' => [\Magento\Framework\Data\Structure::PARENT => 'one'],
                    'four' => [\Magento\Framework\Data\Structure::CHILDREN => []],
                ]
            ],
            [
                [
                    'one' => [
                        \Magento\Framework\Data\Structure::CHILDREN => ['two' => 't.w.o.'],
                        \Magento\Framework\Data\Structure::GROUPS => [
                            'group' => ['two' => 'two', 'three' => 'three'],
                        ],
                    ],
                    'two' => [\Magento\Framework\Data\Structure::PARENT => 'one'],
                    'three' => [],
                ]
            ]
        ];
    }

    /**
     * @param array $elements
     * @return void
     * @dataProvider importExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testImportException($elements)
    {
        $this->_structure->importElements($elements);
    }

    /**
     * @return array
     */
    public function importExceptionDataProvider()
    {
        return [
            'numeric id' => [['element']],
            'completely missing nested set' => [
                ['one' => [\Magento\Framework\Data\Structure::PARENT => 'two'], 'two' => []],
            ],
            'messed up nested set' => [
                [
                    'one' => [\Magento\Framework\Data\Structure::PARENT => 'two'],
                    'two' => [\Magento\Framework\Data\Structure::CHILDREN => ['three' => 't.h.r.e.e.']],
                    'three' => [],
                ],
            ],
            'nested set invalid data type' => [
                ['one' => [\Magento\Framework\Data\Structure::CHILDREN => '']],
            ],
            'duplicate aliases' => [
                [
                    'one' => [
                        \Magento\Framework\Data\Structure::CHILDREN => ['two' => 'alias', 'three' => 'alias'],
                    ],
                    'two' => [\Magento\Framework\Data\Structure::PARENT => 'one'],
                    'three' => [\Magento\Framework\Data\Structure::PARENT => 'one'],
                ],
            ],
            'missing reference back to parent' => [
                ['one' => [
                    \Magento\Framework\Data\Structure::CHILDREN => ['two' => 't.w.o.'], ], 'two' => [],
                ],
            ],
            'broken reference back to parent' => [
                [
                    'one' => [
                        \Magento\Framework\Data\Structure::CHILDREN => ['two' => 't.w.o.', 'three' => 't.h.r.e.e.'],
                    ],
                    'two' => [\Magento\Framework\Data\Structure::PARENT => 'three'],
                    'three' => [\Magento\Framework\Data\Structure::PARENT => 'one'],
                ],
            ],
            'groups invalid data type' => [['one' => [\Magento\Framework\Data\Structure::GROUPS => '']]],
            'group invalid data type' => [
                ['one' => [\Magento\Framework\Data\Structure::GROUPS => [1]]],
            ],
            'asymmetric group' => [
                [
                    'one' => [\Magento\Framework\Data\Structure::GROUPS => ['two' => 'three']],
                    'two' => [],
                    'three' => [],
                ],
            ],
            'group references to non-existing element' => [
                ['one' => [\Magento\Framework\Data\Structure::GROUPS => ['two' => 'two']]],
            ]
        ];
    }

    /**
     * @param array $elements
     * @return void
     * @dataProvider importExceptionElementNotFoundDataProvider
     * @expectedException \OutOfBoundsException
     */
    public function testImportExceptionElementNotFound($elements)
    {
        $this->_structure->importElements($elements);
    }

    /**
     * @return array
     */
    public function importExceptionElementNotFoundDataProvider()
    {
        return [
            'non-existing parent' => [
                ['element' => [\Magento\Framework\Data\Structure::PARENT => 'unknown']],
            ],
            'missing child' => [
                [
                    'one' => [
                        \Magento\Framework\Data\Structure::CHILDREN => ['two' => 't.w.o.', 'three' => 't.h.r.e.e.'],
                    ],
                    'two' => [\Magento\Framework\Data\Structure::PARENT => 'one'],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    public function testCreateGetHasElement()
    {
        $data = [uniqid() => uniqid()];
        $elementId = uniqid('id');
        $this->assertFalse($this->_structure->hasElement($elementId));
        $this->assertFalse($this->_structure->getElement($elementId));

        $this->_structure->createElement($elementId, $data);
        $this->assertTrue($this->_structure->hasElement($elementId));
        $this->assertSame($data, $this->_structure->getElement($elementId));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateElementException()
    {
        $elementId = uniqid('id');
        $this->_structure->createElement($elementId, []);
        $this->_structure->createElement($elementId, []);
    }

    /**
     * @return void
     */
    public function testUnsetElement()
    {
        $this->_populateSampleStructure();

        // non-recursively
        $this->assertTrue($this->_structure->unsetElement('six', false));
        $this->assertFalse($this->_structure->unsetElement('six', false));
        $this->assertSame([5], $this->_structure->getElement('five'));

        // recursively
        $this->assertTrue($this->_structure->unsetElement('three'));
        $this->assertTrue($this->_structure->unsetElement('four'));
        $this->assertSame(['one' => [], 'five' => [5]], $this->_structure->exportElements());
    }

    /**
     * @return void
     */
    public function testSetGetAttribute()
    {
        $this->_populateSampleStructure();
        $this->assertFalse($this->_structure->getAttribute('two', 'non-existing'));
        $this->assertEquals('bar', $this->_structure->getAttribute('two', 'foo'));
        $value = uniqid();
        $this->_structure->setAttribute('two', 'non-existing', $value)->setAttribute('two', 'foo', $value);
        $this->assertEquals($value, $this->_structure->getAttribute('two', 'non-existing'));
        $this->assertEquals($value, $this->_structure->getAttribute('two', 'foo'));
    }

    /**
     * @return void
     * @expectedException \OutOfBoundsException
     */
    public function testSetAttributeNoElementException()
    {
        $this->_structure->setAttribute('non-existing', 'foo', 'bar');
    }

    /**
     * @param string $attribute
     * @return void
     * @expectedException \InvalidArgumentException
     * @dataProvider setAttributeArgumentExceptionDataProvider
     */
    public function testSetAttributeArgumentException($attribute)
    {
        $this->_structure->importElements(['element' => []]);
        $this->_structure->setAttribute('element', $attribute, 'value');
    }

    /**
     * @return array
     */
    public function setAttributeArgumentExceptionDataProvider()
    {
        return [
            [\Magento\Framework\Data\Structure::CHILDREN],
            [\Magento\Framework\Data\Structure::PARENT],
            [\Magento\Framework\Data\Structure::GROUPS]
        ];
    }

    /**
     * @return void
     * @expectedException \OutOfBoundsException
     */
    public function testGetAttributeNoElementException()
    {
        $this->_structure->getAttribute('non-existing', 'foo');
    }

    /**
     * @return void
     */
    public function testRenameElement()
    {
        $this->_populateSampleStructure();

        // rename element and see how children got updated
        $element = $this->_structure->getElement('four');
        $this->assertNotEmpty($element);
        $this->assertFalse($this->_structure->getElement('four.5'));
        $this->assertSame($this->_structure, $this->_structure->renameElement('four', 'four.5'));
        $this->assertSame($element, $this->_structure->getElement('four.5'));
        $this->assertEquals(
            'four.5',
            $this->_structure->getAttribute('two', \Magento\Framework\Data\Structure::PARENT)
        );
        $this->assertEquals(
            'four.5',
            $this->_structure->getAttribute('three', \Magento\Framework\Data\Structure::PARENT)
        );

        // rename element and see how parent got updated
        $this->_structure->renameElement('three', 'three.5');
        // first child
        $this->assertSame(['three.5' => 'th', 'two' => 'tw'], $this->_structure->getChildren('four.5'));
        $this->_structure->renameElement('two', 'two.5');
        // second and last child
        $this->assertSame(['three.5' => 'th', 'two.5' => 'tw'], $this->_structure->getChildren('four.5'));
    }

    /**
     * @return void
     */
    public function testSetAsChild()
    {
        $this->_populateSampleStructure();

        // default alias
        $this->_structure->setAsChild('two', 'one');
        $this->assertEquals('one', $this->_structure->getParentId('two'));
        $this->assertEquals(['two' => 'two'], $this->_structure->getChildren('one'));
        $this->assertEquals(['three' => 'th'], $this->_structure->getChildren('four'));

        // specified alias
        $this->_structure->setAsChild('six', 'three', 's');
        $this->assertEquals('three', $this->_structure->getParentId('six'));
        $this->assertEquals(['six' => 's'], $this->_structure->getChildren('three'));
    }

    /**
     * @param int $offset
     * @param int $expectedOffset
     * @return void
     * @dataProvider setAsChildOffsetDataProvider
     */
    public function testSetAsChildOffset($offset, $expectedOffset)
    {
        $this->_populateSampleSortStructure();
        $this->_structure->setAsChild('x', 'parent', '', $offset);
        $children = $this->_structure->getChildren('parent');
        $actualOffset = array_search('x', array_keys($children));
        $this->assertSame(
            $expectedOffset,
            $actualOffset,
            "The 'x' is expected to be at '{$expectedOffset}' offset, rather than '{$actualOffset}', in array: " .
            var_export(
                $children,
                1
            )
        );
    }

    /**
     * @return array
     */
    public function setAsChildOffsetDataProvider()
    {
        return [
            [0, 0],
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 4],
            [5, 5],
            [null, 5],
            [-1, 4],
            [-2, 3],
            [-3, 2],
            [-4, 1],
            [-5, 0]
        ];
    }

    /**
     * @param string $elementId
     * @param string $parentId
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider setAsChildExceptionDataProvider
     */
    public function testSetAsChildException($elementId, $parentId)
    {
        $this->_structure->createElement('one', []);
        $this->_structure->createElement('two', []);
        $this->_structure->createElement('three', []);
        $this->_structure->setAsChild('three', 'two');
        $this->_structure->setAsChild('two', 'one');
        $this->_structure->setAsChild($elementId, $parentId);
    }

    /**
     * @return array
     */
    public function setAsChildExceptionDataProvider()
    {
        return [['one', 'three'], ['one', 'one']];
    }

    /**
     * @return void
     */
    public function testUnsetChild()
    {
        $this->_populateSampleStructure();

        // specify element by name
        $this->_structure->unsetChild('five');
        $this->assertFalse($this->_structure->getParentId('five'));
        $this->assertArrayNotHasKey(\Magento\Framework\Data\Structure::CHILDREN, $this->_structure->getElement('six'));

        // specify element by parent and alias
        $this->_structure->unsetChild('four', 'tw');
        $this->assertFalse($this->_structure->getChildId('four', 'tw'));
        $this->assertFalse($this->_structure->getParentId('two'));
    }

    /**
     * @param int $initialOffset
     * @param int $newOffset
     * @param int $expectedOffset
     * @return void
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
        return [
            // x* 1 2 3 4 5
            [0, 0, 1],
            [0, 1, 1],
            [0, 2, 2],
            [0, 3, 3],
            [0, +100500, 6],
            [0, -1, 5],
            [0, -4, 2],
            [0, -5, 1],
            [0, -999, 1],
            // 1 x* 2 3 4 5
            [1, 0, 1],
            [1, 1, 2],
            [1, 2, 2],
            [1, 3, 3],
            [1, -1, 5],
            [1, -4, 2],
            [1, -5, 2],
            [1, -6, 1],
            // 1 2 x* 3 4 5
            [2, 0, 1],
            [2, 1, 2],
            [2, 2, 3],
            [2, 3, 3],
            [2, 4, 4],
            [2, null, 6],
            // 1 2 3 4 5 x*
            [5, 0, 1],
            [5, 1, 2],
            [5, 5, 6]
        ];
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testReorderChildException()
    {
        $this->_structure->createElement('one', []);
        $this->_structure->createElement('two', []);
        $this->_structure->reorderChild('one', 'two', 0);
    }

    /**
     * @param int $initialOffset
     * @param string $sibling
     * @param int $delta
     * @param int $expectedOffset
     * @return void
     * @dataProvider reorderSiblingDataProvider
     */
    public function testReorderToSibling($initialOffset, $sibling, $delta, $expectedOffset)
    {
        $this->_populateSampleSortStructure();
        $this->_structure->setAsChild('x', 'parent', '', $initialOffset);
        $this->assertSame($expectedOffset, $this->_structure->reorderToSibling('parent', 'x', $sibling, $delta));
    }

    /**
     * @return array
     */
    public function reorderSiblingDataProvider()
    {
        return [
            // x* 1 2 3 4 5
            [0, 'one', 1, 2],
            [0, 'three', 2, 5],
            [0, 'five', 1, 6],
            [0, 'five', 10, 6],
            [0, 'one', -1, 1],
            [0, 'one', -999, 1],
            // 1 2 x* 3 4 5
            [2, 'two', 1, 3],
            [2, 'two', 2, 4],
            [2, 'two', 3, 5],
            [2, 'two', 999, 6],
            [2, 'two', -1, 2],
            [2, 'two', -2, 1],
            [2, 'two', -999, 1],
            [2, 'x', 1, 3],
            [2, 'x', 2, 4],
            [2, 'x', 3, 5],
            [2, 'x', 999, 6],
            [2, 'x', -1, 3],
            [2, 'x', -2, 2],
            [2, 'x', -999, 1]
        ];
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testReorderToSiblingException()
    {
        $this->_structure->createElement('one', []);
        $this->_structure->createElement('two', []);
        $this->_structure->createElement('three', []);
        $this->_structure->setAsChild('two', 'one');
        $this->_structure->reorderToSibling('one', 'three', 'two', 1);
    }

    /**
     * @return void
     */
    public function testGetChildId()
    {
        $this->_populateSampleStructure();
        $this->assertFalse($this->_structure->getChildId('nonexisting-parent', 'does not matter'));
        $this->assertEquals('five', $this->_structure->getChildId('six', 'f'));
    }

    /**
     * @return void
     */
    public function testGetChildrenParentIdChildAlias()
    {
        $this->_structure->createElement('one', []);
        $this->_structure->createElement('two', []);
        $this->_structure->createElement('three', []);
        $this->_structure->setAsChild('two', 'one');
        $this->_structure->setAsChild('three', 'one', 'th');

        // getChildren()
        $this->assertSame(['two' => 'two', 'three' => 'th'], $this->_structure->getChildren('one'));
        $this->assertSame([], $this->_structure->getChildren('three'));
        $this->assertSame([], $this->_structure->getChildren('nonexisting'));

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
     * @return void
     * @covers \Magento\Framework\Data\Structure::addToParentGroup
     * @covers \Magento\Framework\Data\Structure::getGroupChildNames
     */
    public function testGroups()
    {
        // non-existing element
        $this->assertFalse($this->_structure->addToParentGroup('non-existing', 'group1'));
        $this->assertSame([], $this->_structure->getGroupChildNames('non-existing', 'group1'));

        // not a child
        $this->_structure->createElement('one', []);
        $this->_structure->createElement('two', []);
        $this->assertFalse($this->_structure->addToParentGroup('two', 'group1'));
        $this->assertSame([], $this->_structure->getGroupChildNames('one', 'group1'));

        // child
        $this->_structure->setAsChild('two', 'one');
        $this->assertTrue($this->_structure->addToParentGroup('two', 'group1'));
        $this->assertTrue($this->_structure->addToParentGroup('two', 'group2'));

        // group getter
        $this->_structure->createElement('three', []);
        $this->_structure->createElement('four', []);
        $this->_structure->setAsChild('three', 'one', 'th');
        $this->_structure->setAsChild('four', 'one');
        $this->_structure->addToParentGroup('three', 'group1');
        $this->_structure->addToParentGroup('four', 'group2');
        $this->assertSame(['two', 'three'], $this->_structure->getGroupChildNames('one', 'group1'));
        $this->assertSame(['two', 'four'], $this->_structure->getGroupChildNames('one', 'group2'));

        // unset a child
        $this->_structure->unsetChild('one', 'two');
        $this->assertSame(['three'], $this->_structure->getGroupChildNames('one', 'group1'));
        $this->assertSame(['four'], $this->_structure->getGroupChildNames('one', 'group2'));

        // return child back
        $this->_structure->setAsChild('two', 'one');
        $this->assertSame(['two', 'three'], $this->_structure->getGroupChildNames('one', 'group1'));
        $this->assertSame(['two', 'four'], $this->_structure->getGroupChildNames('one', 'group2'));
    }

    /**
     * Import a sample valid structure
     * @return void
     */
    protected function _populateSampleStructure()
    {
        $this->_structure->importElements(
            [
                'one' => [],
                'two' => [\Magento\Framework\Data\Structure::PARENT => 'four', 'foo' => 'bar'],
                'three' => [\Magento\Framework\Data\Structure::PARENT => 'four', 'bar' => 'baz'],
                'four' => [\Magento\Framework\Data\Structure::CHILDREN => ['three' => 'th', 'two' => 'tw']],
                'five' => [\Magento\Framework\Data\Structure::PARENT => 'six', 5],
                'six' => [\Magento\Framework\Data\Structure::CHILDREN => ['five' => 'f']],
            ]
        );
    }

    /**
     * Import a sample structure, suitable for testing elements sort order
     * @return void
     */
    protected function _populateSampleSortStructure()
    {
        $child = [\Magento\Framework\Data\Structure::PARENT => 'parent'];
        $this->_structure->importElements(
            [
                'parent' => [
                    \Magento\Framework\Data\Structure::CHILDREN => [
                        'one' => 'e1',
                        'two' => 'e2',
                        'three' => 'e3',
                        'four' => 'e4',
                        'five' => 'e5',
                    ],
                ],
                'one' => $child,
                'two' => $child,
                'three' => $child,
                'four' => $child,
                'five' => $child,
                'x' => [],
            ]
        );
    }
}
