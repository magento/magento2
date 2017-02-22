<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Profiler;

/**
 * Test class for \Magento\TestFramework\Profiler\OutputBamboo.
 */
require_once __DIR__ . '/OutputBambooTestFilter.php';
class OutputBambooTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Profiler\OutputBamboo
     */
    protected $_output;

    public static function setUpBeforeClass()
    {
        stream_filter_register('dataCollectorFilter', 'Magento\Test\Profiler\OutputBambooTestFilter');
    }

    /**
     * Reset collected data and prescribe to pass stream data through the collector filter
     */
    protected function setUp()
    {
        \Magento\Test\Profiler\OutputBambooTestFilter::resetCollectedData();

        /**
         * @link http://php.net/manual/en/wrappers.php.php
         */
        $this->_output = new \Magento\TestFramework\Profiler\OutputBamboo(
            [
                'filePath' => 'php://filter/write=dataCollectorFilter/resource=php://memory',
                'metrics' => ['sample metric (ms)' => ['profiler_key_for_sample_metric']],
            ]
        );
    }

    public function testDisplay()
    {
        $this->_output->display(new \Magento\Framework\Profiler\Driver\Standard\Stat());
        \Magento\Test\Profiler\OutputBambooTestFilter::assertCollectedData("Timestamp,\"sample metric (ms)\"\n%d,%d");
    }
}
