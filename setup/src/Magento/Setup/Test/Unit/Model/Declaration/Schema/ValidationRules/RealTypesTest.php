<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Declaration\Schema\ValidationRules;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Declaration\ValidationRules\RealTypes;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

class RealTypesTest extends \PHPUnit\Framework\TestCase
{
    /** @var RealTypes */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp()
    {
        
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            RealTypes::class,
            []
        );
    }

    public function testValidate()
    {
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $okColumn = new Real('decimal', 'decimal', $table, 10, 5);
        $invalidColumn = new Real('float', 'float', $table, 5, 10);
        $table->addColumns([$okColumn, $invalidColumn]);
        /** @var Schema|\PHPUnit_Framework_MockObject_MockObject $schemaMock */
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
                        'Real type "precision" must be greater or equal to "scale". ' .
                        'float(5,10) is invalid in name.float.'
                ]
            ],
            $this->model->validate($schemaMock)
        );
    }
}
