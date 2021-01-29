<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\ValidationRules;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationRules\RealTypes;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Real;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

class RealTypesTest extends \PHPUnit\Framework\TestCase
{
    /** @var RealTypes */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            RealTypes::class,
            []
        );
    }

    public function testValidate()
    {
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf-8',
            ''
        );
        $okColumn = new Real('decimal', 'decimal', $table, 10, 5);
        $invalidColumn = new Real('float', 'float', $table, 5, 10);
        $table->addColumns([$okColumn, $invalidColumn]);
        /** @var Schema|\PHPUnit\Framework\MockObject\MockObject $schemaMock */
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
