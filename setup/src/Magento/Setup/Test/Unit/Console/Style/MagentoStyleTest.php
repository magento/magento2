<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Style;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Console\Style\MagentoStyle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provide tests for MagentoStyle output decorator.
 */
class MagentoStyleTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var MagentoStyle
     */
    private $magentoStyle;

    /**
     * Auxiliary class replacing console output.
     *
     * @var TestOutput
     */
    private $testOutput;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $input = new ArrayInput(['name' => 'foo'], new InputDefinition([new InputArgument('name')]));
        $this->testOutput = new TestOutput();
        $this->magentoStyle = new MagentoStyle($input, $this->testOutput);
    }

    /**
     * Test style decorator will output block with correct style.
     *
     * @return void
     */
    public function testBlockStyle()
    {
        $this->magentoStyle->block(
            ['test first message', 'test second message'],
            'testBlockType',
            'testBlockStyle',
            'testBlockPrefix'
        );
        // @codingStandardsIgnoreStart
        $expected = PHP_EOL . PHP_EOL . PHP_EOL .
            '\<testBlockStyle\>testBlockPrefix\[testBlockType\] test first message\s+'
            . PHP_EOL . '\<testBlockStyle\>testBlockPrefix\s+'
            . PHP_EOL . '\<testBlockStyle\>testBlockPrefix \s+ test second message\s+'
            . PHP_EOL . PHP_EOL;
        // @codingStandardsIgnoreEnd
        $this->assertRegExp('/' . $expected . '/', $this->testOutput->output, 'Block does not match output');
    }

    /**
     * Test style decorator will add title with correct style.
     *
     * @return void
     */
    public function testTitleStyle()
    {
        $this->magentoStyle->title('My Title');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . ' My Title' . PHP_EOL . ' ========' . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->testOutput->output, 'Title does not match output');
    }

    /**
     * Test style decorator will output section with correct style.
     *
     * @return void
     */
    public function testSectionStyle()
    {
        $this->magentoStyle->section('My Section');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . ' My Section' . PHP_EOL . ' ----------' . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->testOutput->output, 'Section does not match output');
    }

    /**
     * Test style decorator will output listing with proper style.
     *
     * @return void
     */
    public function testListingStyle()
    {
        $this->magentoStyle->listing(['test first element', 'test second element']);
        $expected = PHP_EOL . ' * test first element' . PHP_EOL . ' * test second element' . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->testOutput->output, 'Listing does not match output');
    }

    /**
     * Test style decorator will output text with proper style.
     *
     * @return void
     */
    public function testTextStyle()
    {
        $this->magentoStyle->text('test message');
        $expected = PHP_EOL . ' test message' . PHP_EOL;

        $this->assertEquals($expected, $this->testOutput->output, 'Text does not match output');
    }

    /**
     * Test style decorator will output comment with proper style.
     *
     * @return void
     */
    public function testCommentStyle()
    {
        $this->magentoStyle->comment('test comment');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . '\s+test comment\s+' . PHP_EOL . PHP_EOL;
        $this->assertRegExp('/' . $expected . '/', $this->testOutput->output, 'Comment does not match output');
    }

    /**
     * Test style decorator will output success message with proper style.
     *
     * @return void
     */
    public function testSuccessStyle()
    {
        $this->magentoStyle->success('test success message');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . ' \[SUCCESS\] test success message\s+' . PHP_EOL . PHP_EOL;
        $this->assertRegExp('/' . $expected . '/', $this->testOutput->output, 'Success message does not match output');
    }

    /**
     * Test style decorator will output error message with proper style.
     *
     * @return void
     */
    public function testErrorStyle()
    {
        $this->magentoStyle->error('test error message');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . '\s+\[ERROR\] test error message\s+' . PHP_EOL . PHP_EOL;
        $this->assertRegExp('/' . $expected . '/', $this->testOutput->output, 'Error message does not match output');
    }

    /**
     * Test style decorator will output warning message with proper style.
     *
     * @return void
     */
    public function testWarningStyle()
    {
        $this->magentoStyle->warning('test warning message');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . '\s+\[WARNING\] test warning message\s+' . PHP_EOL . PHP_EOL;
        $this->assertRegExp('/' . $expected . '/', $this->testOutput->output, 'Warning message does not match output');
    }

    /**
     * Test style decorator will output note message with proper style.
     *
     * @return void
     */
    public function testNoteStyle()
    {
        $this->magentoStyle->note('test note message');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . '\s+\[NOTE\] test note message\s+' . PHP_EOL . PHP_EOL;
        $this->assertRegExp('/' . $expected . '/', $this->testOutput->output, 'Note message does not match output');
    }

    /**
     * Test style decorator will output caution message with proper style.
     *
     * @return void
     */
    public function testCautionStyle()
    {
        $this->magentoStyle->caution('test caution message');
        $expected = PHP_EOL . PHP_EOL . PHP_EOL . '\s+! \[CAUTION\] test caution message\s+' . PHP_EOL . PHP_EOL;
        $this->assertRegExp('/' . $expected . '/', $this->testOutput->output, 'Caution message does not match output');
    }

    /**
     * Test style decorator will output table with proper style.
     *
     * @return void
     */
    public function testTableStyle()
    {
        $headers = [
            [new TableCell('Main table title', ['colspan' => 2])],
            ['testHeader1', 'testHeader2', 'testHeader3'],
        ];
        $rows = [
            [
                'testValue1',
                'testValue2',
                new TableCell('testValue3', ['rowspan' => 2]),
            ],
            ['testValue4', 'testValue5'],
        ];
        $this->magentoStyle->table($headers, $rows);
        $expected = ' ------------- ------------- ------------- ' . PHP_EOL .
            '  Main table title                         ' . PHP_EOL .
            ' ------------- ------------- ------------- ' . PHP_EOL .
            '  testHeader1   testHeader2   testHeader3  ' . PHP_EOL .
            ' ------------- ------------- ------------- ' . PHP_EOL .
            '  testValue1    testValue2    testValue3   ' . PHP_EOL .
            '  testValue4    testValue5                 ' . PHP_EOL .
            ' ------------- ------------- ------------- ' . PHP_EOL . PHP_EOL;

        $this->assertEquals($expected, $this->testOutput->output, 'Table does not match output');
    }

    /**
     * @return void
     */
    public function testAsk()
    {
        $objectManager = new ObjectManager($this);
        $formatter = $this->getMockBuilder(OutputFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $input = $this->getMockBuilder(InputInterface::class)
            ->setMethods(['isInteractive'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $input->expects($this->exactly(2))
            ->method('isInteractive')
            ->willReturn(false);
        $output = $this->getMockBuilder(OutputInterface::class)
            ->setMethods(['getVerbosity', 'getFormatter'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $output->expects($this->once())
            ->method('getVerbosity')
            ->willReturn(32);
        $output->expects($this->once())
            ->method('getFormatter')
            ->willReturn($formatter);
        $magentoStyle = $objectManager->getObject(
            MagentoStyle::class,
            [
                'input' => $input,
                'output' => $output,
            ]
        );
        $questionHelper = $this->getMockBuilder(SymfonyQuestionHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $questionHelper->expects($this->once())
            ->method('ask')
            ->willReturn('test Answer');
        $objectManager->setBackwardCompatibleProperty($magentoStyle, 'questionHelper', $questionHelper);

        $this->assertEquals(
            'test Answer',
            $magentoStyle->ask('test question?', 'test default')
        );
    }

    /**
     * Test style decorator will output progress with proper style.
     *
     * @return void
     */
    public function testProgress()
    {
        $this->magentoStyle->progressStart(2);
        $this->magentoStyle->progressAdvance(3);
        $this->magentoStyle->progressFinish();
        $expected = ' 0/2 [>                           ]   0%' . PHP_EOL .
            ' 3/3 [============================] 100%' . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->testOutput->output, 'Progress does not match output');
    }
}
