<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Platform\Quote;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\GroupRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRendererTest extends TestCase
{
    /**
     * @var GroupRenderer
     */
    protected $model;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->quoteMock = $this->createPartialMock(Quote::class, ['quoteIdentifier']);
        $this->selectMock = $this->createPartialMock(Select::class, ['getPart']);
        $this->model = $objectManager->getObject(
            GroupRenderer::class,
            ['quote' => $this->quoteMock]
        );
    }

    /**
     * @param array $mapValues
     * @dataProvider renderNoPartDataProvider
     */
    public function testRenderNoPart($mapValues)
    {
        $sql = 'SELECT';
        $this->selectMock->expects($this->any())
            ->method('getPart')
            ->willReturnMap($mapValues);
        $this->assertEquals($sql, $this->model->render($this->selectMock, $sql));
    }

    /**
     * Data provider for testRenderNoPart
     * @return array
     */
    public function renderNoPartDataProvider()
    {
        return [
            [[[Select::FROM, false], [Select::GROUP, false]]],
            [[[Select::FROM, true], [Select::GROUP, false]]],
            [[[Select::FROM, false], [Select::GROUP, true]]],
        ];
    }

    public function testRender()
    {
        $sql = 'SELECT';
        $expectedResult = $sql . ' ' . Select::SQL_GROUP_BY . ' group1' . ",\n\t" . 'group2';
        $mapValues = [
            [Select::FROM, true],
            [Select::GROUP, ['group1', 'group2']]
        ];
        $this->selectMock->expects($this->exactly(3))
            ->method('getPart')
            ->willReturnMap($mapValues);
        $this->quoteMock->expects($this->exactly(2))
            ->method('quoteIdentifier')
            ->willReturnArgument(0);
        $this->assertEquals($expectedResult, $this->model->render($this->selectMock, $sql));
    }
}
