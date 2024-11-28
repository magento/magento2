<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\HavingRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HavingRendererTest extends TestCase
{
    /**
     * @var HavingRenderer
     */
    protected $model;

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
        $this->selectMock = $this->createPartialMock(Select::class, ['getPart']);
        $this->model = $objectManager->getObject(HavingRenderer::class);
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
    public static function renderNoPartDataProvider()
    {
        return [
            [[[Select::FROM, false], [Select::HAVING, false]]],
            [[[Select::FROM, true], [Select::HAVING, false]]],
            [[[Select::FROM, false], [Select::HAVING, true]]],
        ];
    }

    public function testRender()
    {
        $sql = 'SELECT';
        $expectedResult = $sql . ' ' . Select::SQL_HAVING . ' having1 having2';
        $mapValues = [
            [Select::FROM, true],
            [Select::HAVING, ['having1', 'having2']]
        ];
        $this->selectMock->expects($this->any())
            ->method('getPart')
            ->willReturnMap($mapValues);
        $this->assertEquals($expectedResult, $this->model->render($this->selectMock, $sql));
    }
}
