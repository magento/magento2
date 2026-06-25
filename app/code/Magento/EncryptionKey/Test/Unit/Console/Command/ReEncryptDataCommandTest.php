<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Test\Unit\Console\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\EncryptionKey\Model\Data\ReEncryptorList;
use Magento\EncryptionKey\Console\Command\ReEncryptDataCommand;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\Handler\Error;

/**
 * Test for the 'encryption:data:re-encrypt' CLI command.
 */
class ReEncryptDataCommandTest extends TestCase
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
        $reEncryptorError = $this->createMock(Error::class);
        $reEncryptorError->expects($this->any())
            ->method('getRowIdField')
            ->willReturn("id");
        $reEncryptorError->expects($this->any())
            ->method('getRowIdValue')
            ->willReturn(1);
        $reEncryptorError->expects($this->any())
            ->method('getMessage')
            ->willReturn("Test error");

        $reEncryptorOneMock = $this->createMock(ReEncryptor::class);
        $reEncryptorOneMock->expects($this->any())
            ->method('reEncrypt')
            ->willReturn([$reEncryptorError]);

        $reEncryptorTwoMock = $this->createMock(ReEncryptor::class);
        $reEncryptorTwoMock->expects($this->any())
            ->method('reEncrypt')
            ->willReturn([]);

        $reEncryptorThreeMock = $this->createMock(ReEncryptor::class);
        $reEncryptorThreeMock->expects($this->any())
            ->method('reEncrypt')
            ->willThrowException(new \Exception("Critical error!"));

        $reEncryptorListMock = $this->createMock(ReEncryptorList::class);
        $reEncryptorListMock->expects($this->any())
            ->method('getReEncryptors')
            ->willReturn(
                [
                    "test_one" => $reEncryptorOneMock,
                    "test_two" => $reEncryptorTwoMock,
                    "test_three" => $reEncryptorThreeMock
                ]
            );

        $this->commandTester = new CommandTester(
            new ReEncryptDataCommand($reEncryptorListMock)
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
                "%s\n%s\n%s\n%s\n%s\n%s\n%s\n%s\n",
                "Executing 'test_one' re-encryptor...",
                "Done in 0:00:00 but with the following errors:",
                "[id 1]: Test error",
                "Executing 'test_two' re-encryptor...",
                "Done successfully in 0:00:00.",
                "Executing 'test_three' re-encryptor...",
                "Failed due to the following error:",
                "Critical error!"
            ),
            $this->commandTester->getDisplay()
        );

        $this->commandTester->execute(["encryptors" => ["test_two"]]);

        $this->assertEquals(
            sprintf(
                "%s\n%s\n",
                "Executing 'test_two' re-encryptor...",
                "Done successfully in 0:00:00."
            ),
            $this->commandTester->getDisplay()
        );

        $this->commandTester->execute(["encryptors" => ["test_four"]]);

        $this->assertEquals(
            sprintf(
                "%s\n",
                "Re-encryptor 'test_four' could not be found!"
            ),
            $this->commandTester->getDisplay()
        );
    }
}
