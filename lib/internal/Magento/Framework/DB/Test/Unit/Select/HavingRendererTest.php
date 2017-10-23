<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;

/**
 * Class HavingRendererTest
 */
class HavingRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Select\HavingRenderer
     */
    protected $model;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->selectMock = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['getPart']);
        $this->model = $objectManager->getObject(\Magento\Framework\DB\Select\HavingRenderer::class);
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
