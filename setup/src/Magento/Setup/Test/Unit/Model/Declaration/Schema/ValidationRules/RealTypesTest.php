<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Declaration\ValidationRules;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

class RealTypesTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Setup\Model\Declaration\Schema\Declaration\ValidationRules\RealTypes */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Setup\Model\Declaration\Schema\Declaration\ValidationRules\RealTypes::class,
            []
        );
    }

    public function testValidate()
    {
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $okColumn = new Real('decimal', 'decimal', $table, 10, 5);
        $invalidColumn = new Real('float', 'float', $table, 5, 10);
        $table->addColumns([$okColumn, $invalidColumn]);
        $schemaMock = $this->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaMock->expects(self::once())
            ->method('getTables')
            ->willReturn([$table]);

        self::assertEquals(
            [
                [
                    'column' => 'name.float',
                    'message' =>
                        'Real type "scale" must be greater or equal to "precision". ' .
                        'float(10,5) is invalid in name.float.'
                ]
            ],
            $this->model->validate($schemaMock)
        );
    }
}
