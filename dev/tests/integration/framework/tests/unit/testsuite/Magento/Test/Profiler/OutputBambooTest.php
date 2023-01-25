<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Profiler;

/**
 * Test class for \Magento\TestFramework\Profiler\OutputBamboo.
 */
require_once __DIR__ . '/OutputBambooTestFilter.php';
class OutputBambooTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Profiler\OutputBamboo
     */
    protected $_output;

    public static function setUpBeforeClass(): void
    {
        stream_filter_register('dataCollectorFilter', \Magento\Test\Profiler\OutputBambooTestFilter::class);
    }

    /**
     * Reset collected data and prescribe to pass stream data through the collector filter
     */
    protected function setUp(): void
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
