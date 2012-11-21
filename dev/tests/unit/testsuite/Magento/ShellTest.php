<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Shell
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_ShellTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetVerbose()
    {
        $shell = new Magento_Shell(false);
        $this->assertFalse($shell->getVerbose());

        $shell->setVerbose(true);
        $this->assertTrue($shell->getVerbose());

        $shell->setVerbose(false);
        $this->assertFalse($shell->getVerbose());
    }

    /**
     * @param string $phpCommand
     * @param bool $isVerbose
     * @param string $expectedOutput
     * @param string $expectedResult
     * @dataProvider executeDataProvider
     */
    public function testExecute($phpCommand, $isVerbose, $expectedOutput, $expectedResult = '')
    {
        $this->expectOutputString($expectedOutput);
        $shell = new Magento_Shell($isVerbose);
        $actualResult = $shell->execute('php -r %s', array($phpCommand));
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function executeDataProvider()
    {
        $quote = substr(escapeshellarg(' '), 0, 1);
        $eol = PHP_EOL;
        return array(
            'capture STDOUT' => array(
                'echo 27181;', false, '', '27181',
            ),
            'print STDOUT' => array(
                'echo 27182;', true, "php -r {$quote}echo 27182;{$quote} 2>&1{$eol}27182{$eol}", '27182',
            ),
            'capture STDERR' => array(
                'fwrite(STDERR, 27183);', false, '', '27183',
            ),
            'print STDERR' => array(
                'fwrite(STDERR, 27184);', true, "php -r {$quote}fwrite(STDERR, 27184);{$quote} 2>&1{$eol}27184{$eol}",
                '27184',
            ),
        );
    }

    /**
     * @expectedException Magento_Exception
     * @expectedExceptionMessage Command `non_existing_command 2>&1` returned non-zero exit code
     * @expectedExceptionCode 0
     */
    public function testExecuteFailure()
    {
        $shell = new Magento_Shell();
        $shell->execute('non_existing_command');
    }

    /**
     * @param string $phpCommand
     * @param bool $isVerbose
     * @param string $expectedOutput
     * @param string $expectedError
     * @dataProvider executeDataProvider
     */
    public function testExecuteFailureDetails($phpCommand, $isVerbose, $expectedOutput, $expectedError)
    {
        try {
            /* Force command to return non-zero exit code */
            $phpFailingCommand = $phpCommand . ' exit(42);';
            $expectedOutput = str_replace($phpCommand, $phpFailingCommand, $expectedOutput);
            $this->testExecute($phpFailingCommand, $isVerbose, $expectedOutput);
        } catch (Magento_Exception $e) {
            $this->assertInstanceOf('Exception', $e->getPrevious());
            $this->assertEquals($expectedError, $e->getPrevious()->getMessage());
            $this->assertEquals(42, $e->getPrevious()->getCode());
        }
    }
}
