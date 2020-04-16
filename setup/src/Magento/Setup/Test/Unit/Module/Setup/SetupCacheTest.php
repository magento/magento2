<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Setup;

use Magento\Setup\Module\Setup\SetupCache;

class SetupCacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SetupCache
     */
    private $object;

    protected function setUp(): void
    {
        $this->object = new SetupCache();
    }

    public function testSetRow()
    {
        $table = 'table';
        $parentId = 'parent';
        $rowId = 'row';
        $data = new \stdClass();

        $this->object->setRow($table, $parentId, $rowId, $data);
        $this->assertSame($data, $this->object->get($table, $parentId, $rowId));
    }

    public function testSetField()
    {
        $table = 'table';
        $parentId = 'parent';
        $rowId = 'row';
        $field = 'field';
        $data = new \stdClass();

        $this->object->setField($table, $parentId, $rowId, $field, $data);
        $this->assertSame($data, $this->object->get($table, $parentId, $rowId, $field));
    }

    /**
     * @dataProvider getNonexistentDataProvider
     * @param string $field
     */
    public function testGetNonexistent($field)
    {
        $this->assertFalse($this->object->get('table', 'parent', 'row', $field));
    }

    /**
     * @return array
     */
    public function getNonexistentDataProvider()
    {
        return [
            [null],
            ['field'],
        ];
    }

    public function testRemove()
    {
        $table = 'table';
        $parentId = 'parent';
        $rowId = 'row';
        $data = new \stdClass();

        $this->object->setRow($table, $parentId, $rowId, $data);
        $this->object->remove($table, $parentId, $rowId, $data);
        $this->assertFalse($this->object->get($table, $parentId, $rowId));
    }

    /**
     * @dataProvider hasDataProvider
     * @param string $table
     * @param string $parentId
     * @param string $rowId
     * @param string $field
     * @param bool $expected
     */
    public function testHas($table, $parentId, $rowId, $field, $expected)
    {
        $this->object->setField('table', 'parent', 'row', 'field', 'data');
        $this->assertSame($expected, $this->object->has($table, $parentId, $rowId, $field));
    }

    /**
     * @return array
     */
    public function hasDataProvider()
    {
        return [
            'existing'           => ['table', 'parent', 'row', 'field', true],
            'nonexistent field'  => ['table', 'parent', 'row', 'other_field', false],
            'nonexistent row'    => ['table', 'parent', 'other_row', 'field', false],
            'nonexistent parent' => ['table', 'other_parent', 'row', 'field', false],
            'nonexistent table'  => ['other_table', 'parent', 'row', 'field', false],
        ];
    }
}
