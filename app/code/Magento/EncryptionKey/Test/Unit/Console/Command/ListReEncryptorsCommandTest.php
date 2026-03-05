<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Test\Unit\Console\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\EncryptionKey\Model\Data\ReEncryptorList;
use Magento\EncryptionKey\Console\Command\ListReEncryptorsCommand;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor;

/**
 * Test for the 'encryption:data:list-re-encryptors' CLI command.
 */
class ListReEncryptorsCommandTest extends TestCase
{
    /**
     * @var CommandTester
     */
    private CommandTester $commandTester;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $reEncryptorOneMock = $this->createMock(ReEncryptor::class);
        $reEncryptorOneMock->expects($this->any())
            ->method('getDescription')
            ->willReturn("Re-encrypts 'test' column in the 'test_one' DB table.");

        $reEncryptorTwoMock = $this->createMock(ReEncryptor::class);
        $reEncryptorTwoMock->expects($this->any())
            ->method('getDescription')
            ->willReturn("Re-encrypts 'test' column in the 'test_two' DB table.");

        $reEncryptorListMock = $this->createMock(ReEncryptorList::class);
        $reEncryptorListMock->expects($this->any())
            ->method('getReEncryptors')
            ->willReturn(
                [
                    "test_one" => $reEncryptorOneMock,
                    "test_two" => $reEncryptorTwoMock
                ]
            );

        $this->commandTester = new CommandTester(
            new ListReEncryptorsCommand($reEncryptorListMock)
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(
            sprintf(
                "%-40s %s\n%-40s %s\n",
                "test_one",
                "Re-encrypts 'test' column in the 'test_one' DB table.",
                "test_two",
                "Re-encrypts 'test' column in the 'test_two' DB table."
            ),
            $this->commandTester->getDisplay()
        );
    }
}
