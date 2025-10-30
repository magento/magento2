<?php declare(strict_types=1);
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard\AbstractOutput
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver\Standard;

use Magento\Framework\Profiler\Driver\Standard\AbstractOutput;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OutputAbstractTest extends TestCase
{
    /**
     * @var AbstractOutput|MockObject
     */
    protected $_output;

    protected function setUp(): void
    {
        $this->_output = $this->getMockForAbstractClass(
            AbstractOutput::class
        );
    }

    /**
     * Test setFilterPattern method
     */
    public function testSetFilterPattern()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $this->assertObjectHasAttribute('_filterPattern', $this->_output);
        $this->assertEmpty($this->_output->getFilterPattern());
        $filterPattern = '/test/';
        $this->_output->setFilterPattern($filterPattern);
        $this->assertEquals($filterPattern, $this->_output->getFilterPattern());
    }

    /**
     * Test setThreshold method
     */
    public function testSetThreshold()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $thresholdKey = Stat::TIME;
        $this->_output->setThreshold($thresholdKey, 100);
        $this->assertObjectHasAttribute('_thresholds', $this->_output);
        $thresholds = $this->_output->getThresholds();
        $this->assertArrayHasKey($thresholdKey, $thresholds);
        $this->assertEquals(100, $thresholds[$thresholdKey]);

        $this->_output->setThreshold($thresholdKey, null);
        $this->assertArrayNotHasKey($thresholdKey, $this->_output->getThresholds());
    }

    /**
     * Test __construct method
     */
    public function testConstructor()
    {
        $configuration = ['filterPattern' => '/filter pattern/', 'thresholds' => ['fetchKey' => 100]];
        /** @var \Magento\Framework\Profiler\Driver\Standard\AbstractOutput $output  */
        $output = $this->getMockForAbstractClass(
            AbstractOutput::class,
            [$configuration]
        );
        $this->assertEquals('/filter pattern/', $output->getFilterPattern());
        $thresholds = $output->getThresholds();
        $this->assertArrayHasKey('fetchKey', $thresholds);
        $this->assertEquals(100, $thresholds['fetchKey']);
    }

    /**
     * Test _renderColumnValue method
     *
     * @dataProvider renderColumnValueDataProvider
     * @param mixed $value
     * @param string $columnKey
     * @param mixed $expectedValue
     */
    public function testRenderColumnValue($value, $columnKey, $expectedValue)
    {
        $method = new \ReflectionMethod($this->_output, '_renderColumnValue');
        $method->setAccessible(true);
        $this->assertEquals($expectedValue, $method->invoke($this->_output, $value, $columnKey));
    }

    /**
     * @return array
     */
    public static function renderColumnValueDataProvider()
    {
        return [
            ['someTimerId', Stat::ID, 'someTimerId'],
            [10000.123, Stat::TIME, '10,000.123000'],
            [200000.123456789, Stat::AVG, '200,000.123457'],
            [1000000000.12345678, Stat::EMALLOC, '1,000,000,000'],
            [2000000000.12345678, Stat::REALMEM, '2,000,000,000']
        ];
    }

    /**
     * Test _renderCaption method
     */
    public function testRenderCaption()
    {
        $method = new \ReflectionMethod($this->_output, '_renderCaption');
        $method->setAccessible(true);
        $this->assertMatchesRegularExpression(
            '/Code Profiler \(Memory usage: real - \d+, emalloc - \d+\)/',
            $method->invoke($this->_output)
        );
    }

    /**
     * Test _getTimerIds method
     */
    public function testGetTimerIds()
    {
        $this->_output->setFilterPattern('/filter pattern/');

        $mockStat = $this->createMock(Stat::class);
        $expectedTimerIds = ['test'];
        $mockStat->expects(
            $this->once()
        )->method(
            'getFilteredTimerIds'
        )->with(
            $this->_output->getThresholds(),
            $this->_output->getFilterPattern()
        )->willReturn(
            $expectedTimerIds
        );

        $method = new \ReflectionMethod($this->_output, '_getTimerIds');
        $method->setAccessible(true);
        $this->assertEquals($expectedTimerIds, $method->invoke($this->_output, $mockStat));
    }

    /**
     * Test _renderTimerId method
     */
    public function testRenderTimerId()
    {
        $method = new \ReflectionMethod($this->_output, '_renderTimerId');
        $method->setAccessible(true);
        $this->assertEquals('someTimerId', $method->invoke($this->_output, 'someTimerId'));
    }
}
