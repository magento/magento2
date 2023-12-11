<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Console\Command\StoreListCommand;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StoreListCommandTest extends TestCase
{
    /**
     * @var StoreListCommand
     */
    private $command;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->command = $this->objectManager->getObject(
            StoreListCommand::class,
            ['storeManager' => $this->storeManagerMock]
        );
    }

    public function testExecuteExceptionNoVerbosity()
    {
        $this->storeManagerMock->expects($this->any())
            ->method('getStores')
            ->willThrowException(new \Exception("Dummy test exception"));

        $tester = new CommandTester($this->command);
        $this->assertEquals(Cli::RETURN_FAILURE, $tester->execute([]));

        $linesOutput = array_filter(explode(PHP_EOL, $tester->getDisplay()));
        $this->assertEquals('Dummy test exception', $linesOutput[0]);
    }

    public function testExecute()
    {
        $storeData = [
            'store_id' => '999',
            'group_id' => '777',
            'website_id' => '888',
            'name' => 'unit test store',
            'code' => 'unit_test_store',
            'is_active' => '1',
            'sort_order' => '123',
        ];

        $stores = [
            $this->objectManager->getObject(Store::class)->setData($storeData),
        ];

        $this->storeManagerMock->expects($this->any())
            ->method('getStores')
            ->willReturn($stores);

        $tester = new CommandTester($this->command);
        $this->assertEquals(Cli::RETURN_SUCCESS, $tester->execute([]));

        $linesOutput = array_filter(explode(PHP_EOL, $tester->getDisplay()));
        $this->assertCount(5, $linesOutput, 'There should be 5 lines output. 3 Spacers, 1 header, 1 content.');

        $this->assertEquals($linesOutput[0], $linesOutput[2], "Lines 0, 2, 4 should be spacer lines");
        $this->assertEquals($linesOutput[2], $linesOutput[4], "Lines 0, 2, 4 should be spacer lines");

        $headerValues = array_values(array_filter(explode('|', $linesOutput[1])));
        //trim to remove the whitespace left from the exploding pipe separation
        $this->assertEquals('ID', trim($headerValues[0]));
        $this->assertEquals('Website ID', trim($headerValues[1]));
        $this->assertEquals('Group ID', trim($headerValues[2]));
        $this->assertEquals('Name', trim($headerValues[3]));
        $this->assertEquals('Code', trim($headerValues[4]));
        $this->assertEquals('Sort Order', trim($headerValues[5]));
        $this->assertEquals('Is Active', trim($headerValues[6]));

        $storeValues = array_values(array_filter(explode('|', $linesOutput[3])));
        $this->assertEquals('999', trim($storeValues[0]));
        $this->assertEquals('888', trim($storeValues[1]));
        $this->assertEquals('777', trim($storeValues[2]));
        $this->assertEquals('unit test store', trim($storeValues[3]));
        $this->assertEquals('unit_test_store', trim($storeValues[4]));
        $this->assertEquals('123', trim($storeValues[5]));
        $this->assertEquals('1', trim($storeValues[6]));
    }
}
