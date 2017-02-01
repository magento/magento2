<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\Config;

use Magento\Analytics\ReportXml\Config\Mapper;

/**
 * Class MapperTest
 */
class MapperTest extends \PHPUnit_Framework_TestCase
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
