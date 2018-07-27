<?php
/**
 * Test case for \Magento\Framework\Profiler\Driver\Standard\Output\Html
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

class HtmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Output\Html
     */
    protected $_output;

    protected function setUp()
    {
        $this->_output = new \Magento\Framework\Profiler\Driver\Standard\Output\Html();
    }

    /**
     * Test display method
     *
     * @dataProvider displayDataProvider
     * @param string $statFile
     * @param string $expectedHtmlFile
     */
    public function testDisplay($statFile, $expectedHtmlFile)
    {
        $stat = include $statFile;
        $expectedHtml = file_get_contents($expectedHtmlFile);

        ob_start();
        $this->_output->display($stat);
        $actualHtml = ob_get_clean();

        $this->_assertDisplayResultEquals($actualHtml, $expectedHtml);
    }

    /**
     * @return array
     */
    public function displayDataProvider()
    {
        return [
            ['statFile' => __DIR__ . '/_files/timers.php', 'expectedHtmlFile' => __DIR__ . '/_files/output.html']
        ];
    }

    /**
     * Asserts display() result equals
     *
     * @param string $actualHtml
     * @param string $expectedHtml
     */
    protected function _assertDisplayResultEquals($actualHtml, $expectedHtml)
    {
        $expectedHtml = ltrim(preg_replace('/^<!--.+?-->/s', '', $expectedHtml));
        if (preg_match('/Code Profiler \(Memory usage: real - (\d+), emalloc - (\d+)\)/', $actualHtml, $matches)) {
            list(, $realMemory, $emallocMemory) = $matches;
            $expectedHtml = str_replace(
                ['%real_memory%', '%emalloc_memory%'],
                [$realMemory, $emallocMemory],
                $expectedHtml
            );
        }
        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
