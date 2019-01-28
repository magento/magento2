<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Console\Command;

use Magento\Store\Console\Command\WebsiteListCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Helper\HelperSet;
use Magento\Store\Model\Website;
use Magento\Framework\Console\Cli;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * @package Magento\Store\Test\Unit\Console\Command
 */
class WebsiteListCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WebsiteListCommand
     */
    private $command;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteRepositoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->websiteRepositoryMock = $this->getMockForAbstractClass(WebsiteRepositoryInterface::class);

        $this->command = $this->objectManager->getObject(
            WebsiteListCommand::class,
            ['websiteManagement' => $this->websiteRepositoryMock]
        );
    }

    public function testExecuteExceptionNoVerbosity()
    {
        $this->websiteRepositoryMock->expects($this->any())
            ->method('getList')
            ->willThrowException(new \Exception("Dummy test exception"));

        $tester = new CommandTester($this->command);
        $this->assertSame(Cli::RETURN_FAILURE, $tester->execute([]));

        $linesOutput = array_filter(explode(PHP_EOL, $tester->getDisplay()));
        $this->assertSame('Dummy test exception', $linesOutput[0]);
    }

    public function testExecute()
    {
        $websiteData = [
            'id' => '444',
            'default_group_id' => '555',
            'name' => 'unit test website',
            'code' => 'unit_test_website',
            'is_default' => '0',
            'sort_order' => '987',
        ];

        $websites = [
            $this->objectManager->getObject(Website::class)->setData($websiteData),
        ];

        $this->websiteRepositoryMock->expects($this->any())
            ->method('getList')
            ->willReturn($websites);

        $tester = new CommandTester($this->command);
        $this->assertSame(Cli::RETURN_SUCCESS, $tester->execute([]));

        $linesOutput = array_filter(explode(PHP_EOL, $tester->getDisplay()));
        $this->assertCount(5, $linesOutput, 'There should be 5 lines output. 3 Spacers, 1 header, 1 content.');

        $this->assertSame($linesOutput[0], $linesOutput[2], "Lines 0, 2, 4 should be spacer lines");
        $this->assertSame($linesOutput[2], $linesOutput[4], "Lines 0, 2, 4 should be spacer lines");

        $headerValues = array_values(array_filter(explode('|', $linesOutput[1])));
        //trim to remove the whitespace left from the exploding pipe separation
        $this->assertSame('ID', trim($headerValues[0]));
        $this->assertSame('Default Group Id', trim($headerValues[1]));
        $this->assertSame('Name', trim($headerValues[2]));
        $this->assertSame('Code', trim($headerValues[3]));
        $this->assertSame('Sort Order', trim($headerValues[4]));
        $this->assertSame('Is Default', trim($headerValues[5]));

        $websiteValues = array_values(array_filter(explode('|', $linesOutput[3])));
        $this->assertSame('444', trim($websiteValues[0]));
        $this->assertSame('555', trim($websiteValues[1]));
        $this->assertSame('unit test website', trim($websiteValues[2]));
        $this->assertSame('unit_test_website', trim($websiteValues[3]));
        $this->assertSame('987', trim($websiteValues[4]));
        $this->assertSame('0', trim($websiteValues[5]));
    }
}
