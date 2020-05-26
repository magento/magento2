<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\Config;

use Magento\Analytics\ReportXml\Config\Mapper;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = new Mapper();
    }

    public function testExecute()
    {
        $configData['config'][0]['report'] = [
            [
                'source' => ['product'],
                'name' => 'Product',
            ]
        ];
        $expectedResult = [
            'Product' => [
                'source' => 'product',
                'name' => 'Product',
            ]
        ];
        $this->assertEquals($this->mapper->execute($configData), $expectedResult);
    }

    public function testExecuteWithoutReports()
    {
        $configData = [];
        $this->assertEquals($this->mapper->execute($configData), []);
    }
}
