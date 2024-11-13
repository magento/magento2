<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Increment;

use Magento\Eav\Model\Entity\Increment\Alphanum;
use PHPUnit\Framework\TestCase;

class AlphanumTest extends TestCase
{
    /**
     * @var Alphanum
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new Alphanum();
    }

    public function testGetAllowedChars()
    {
        $this->assertEquals('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->model->getAllowedChars());
    }

    /**
     * @param int $lastId
     * @param string $prefix
     * @param int|string $expectedResult
     * @dataProvider getLastIdDataProvider
     */
    public function testGetNextId($lastId, $prefix, $expectedResult)
    {
        $this->model->setPrefix($prefix);
        $this->model->setLastId($lastId);
        $this->assertEquals($expectedResult, $this->model->getNextId());
    }

    /**
     * @return array
     */
    public static function getLastIdDataProvider()
    {
        return [
            [
                'lastId' => 'prefix00000001CZ',
                'prefix' => 'prefix',
                'expectedResult' => 'prefix00000001D0',
            ],
            [
                'lastId' => 1,
                'prefix' => 'prefix',
                'expectedResult' => 'prefix00000002'
            ],
        ];
    }

    public function testGetNextIdThrowsExceptionIfIdContainsNotAllowedCharacters()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid character encountered in increment ID: ---wrong-id---');
        $this->model->setLastId('---wrong-id---');
        $this->model->setPrefix('prefix');
        $this->model->getNextId();
    }
}
