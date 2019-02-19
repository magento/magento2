<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model\Search\Config\Structure\Element\Builder;

use Magento\Backend\Model\Search\Config\Structure\Element\Builder\Field;
use Magento\Config\Model\Config\StructureElementInterface;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**
     * @var StructureElementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureElementMock;

    /**
     * @var Field
     */
    private $model;

    protected function setUp()
    {
        $this->structureElementMock = $this->getMockForAbstractClass(StructureElementInterface::class);
        $this->model = new Field();
    }

    public function testBuild()
    {
        $structureElementId = 42;
        $structureElementPath = 'path_part_1/path_part_2';

        $this->structureElementMock->expects($this->once())
            ->method('getId')
            ->willReturn($structureElementId);
        $this->structureElementMock->expects($this->once())
            ->method('getPath')
            ->willReturn($structureElementPath);
        $this->assertEquals(
            [
                'section' => 'path_part_1',
                'group'   => 'path_part_2',
                'field'   => $structureElementId,
            ],
            $this->model->build($this->structureElementMock)
        );
    }
}
