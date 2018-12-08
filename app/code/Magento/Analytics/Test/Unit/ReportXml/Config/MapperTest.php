<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\Config;

use Magento\Analytics\ReportXml\Config\Mapper;

<<<<<<< HEAD
/**
 * Class MapperTest
 */
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp()
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
