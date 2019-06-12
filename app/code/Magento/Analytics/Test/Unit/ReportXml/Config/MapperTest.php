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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
