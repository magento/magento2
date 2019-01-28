<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Increment;

use Magento\Eav\Model\Entity\Increment\NumericValue;

class NumericTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NumericValue
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\Eav\Model\Entity\Increment\NumericValue();
    }

    /**
     * @param int $lastId
     * @param string $prefix
     * @param int|string $expectedResult
     * @dataProvider getLastIdDataProvider
     */
    public function testGetNextId($lastId, $prefix, $expectedResult)
    {
        $this->model->setLastId($lastId);
        $this->model->setPrefix($prefix);
        $this->assertSame($expectedResult, $this->model->getNextId());
    }

    /**
     * @return array
     */
    public function getLastIdDataProvider()
    {
        return [
            [
                'lastId' => 1,
                'prefix' => 'prefix',
                'expectedResult' => 'prefix00000002',
            ],
            [
                'lastId' => 'prefix00000001',
                'prefix' => 'prefix',
                'expectedResult' => 'prefix00000002'
            ],
        ];
    }

    public function testGetPadLength()
    {
        $this->assertSame(8, $this->model->getPadLength());
        $this->model->setPadLength(10);
        $this->assertSame(10, $this->model->getPadLength());
    }

    public function getPadChar()
    {
        $this->assertSame('0', $this->model->getPadChar());
        $this->model->setPadChar('z');
        $this->assertSame('z', $this->model->getPadChar());
    }

    public function testFormat()
    {
        $this->model->setPrefix('prefix');
        $this->model->setPadLength(3);
        $this->model->setPadChar('z');
        $this->assertSame('prefixzz1', $this->model->format(1));
    }

    public function testFrontendFormat()
    {
        $this->assertSame('value', $this->model->frontendFormat('value'));
    }
}
