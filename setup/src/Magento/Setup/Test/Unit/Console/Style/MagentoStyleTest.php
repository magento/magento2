<?php
/**
 * Created by PhpStorm.
 * User: weswe
 * Date: 10/16/2017
 * Time: 5:31 PM
 */

namespace Magento\Setup\Test\Unit\Console\Style;

use Magento\Setup\Console\Style\MagentoStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\Output;

class MagentoStyleTest extends \PHPUnit\Framework\TestCase
{
    public function testTitleStyle()
    {
        $input = new ArrayInput(array('name' => 'foo'), new InputDefinition(array(new InputArgument('name'))));
        $output = new TestOutput();

        $io = new MagentoStyle($input,$output);

        $io->title("My Title");

        $expected = "\r\n My Title\n ========\n\r\n";

        $this->assertEquals($expected,$output->output,"Title does not match output");
    }

    public function testSectionStyle()
    {
        $input = new ArrayInput(array('name' => 'foo'), new InputDefinition(array(new InputArgument('name'))));
        $output = new TestOutput();

        $io = new MagentoStyle($input,$output);

        $io->section("My Section");

        $expected = "\r\n My Section\n ----------\n\r\n";

        $this->assertEquals($expected,$output->output,"Section does not match output");
    }

}

class TestOutput extends Output
{
    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    protected function doWrite($message, $newline)
    {
        $this->output .= $message.($newline ? "\n" : '');
    }
}