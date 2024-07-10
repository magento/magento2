<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\SourceThemeDeployCommand;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Validator\Locale;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see \Magento\Developer\Console\Command\SourceThemeDeployCommand
 */
class SourceThemeDeployCommandTest extends TestCase
{
    public const AREA_TEST_VALUE = 'area-test-value';

    public const LOCALE_TEST_VALUE = 'locale-test-value';

    public const THEME_TEST_VALUE = 'Vendor/theme';

    public const THEME_INCORRECT_FORMAT_VALUE = 'theme-value';

    public const THEME_NONEXISTING_VALUE = 'NonExistentVendor/theme';

    public const TYPE_TEST_VALUE = 'type-test-value';

    public const FILE_TEST_VALUE = 'file-test-value/test/file';

    /**
     * @var SourceThemeDeployCommand
     */
    private $sourceThemeDeployCommand;

    /**
     * @var Locale|MockObject
     */
    private $validatorMock;

    /**
     * @var Publisher|MockObject
     */
    private $assetPublisherMock;

    /**
     * @var Repository|MockObject
     */
    private $assetRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockBuilder(Locale::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetPublisherMock = $this->getMockBuilder(Publisher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sourceThemeDeployCommand = new SourceThemeDeployCommand(
            $this->validatorMock,
            $this->assetPublisherMock,
            $this->assetRepositoryMock
        );
    }

    /**
     * Run test for execute method
     *
     * @return void
     */
    public function testExecute(): void
    {
        /** @var OutputInterface|MockObject $outputMock */
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $assetMock = $this->getMockBuilder(LocalInterface::class)
            ->getMockForAbstractClass();

        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with(self::LOCALE_TEST_VALUE)
            ->willReturn(true);

        $message = sprintf(
            '<info>Processed Area: %s, Locale: %s, Theme: %s, File type: %s.</info>',
            self::AREA_TEST_VALUE,
            self::LOCALE_TEST_VALUE,
            self::THEME_TEST_VALUE,
            self::TYPE_TEST_VALUE
        );

        $outputMock
            ->method('writeln')
            ->willReturnCallback(function ($arg1) use ($message) {
                if ($arg1 == $message ||
                    $arg1 == '<comment>-> file-test-value/test/file</comment>' ||
                    $arg1 == '<info>Successfully processed.</info>'
                ) {
                    return null;
                }
            });

        $this->assetRepositoryMock->expects($this->once())
            ->method('createAsset')
            ->with(
                'file-test-value/test' . DIRECTORY_SEPARATOR . 'file' . '.' . self::TYPE_TEST_VALUE,
                [
                    'area' => self::AREA_TEST_VALUE,
                    'theme' => self::THEME_TEST_VALUE,
                    'locale' => self::LOCALE_TEST_VALUE,
                ]
            )->willReturn($assetMock);

        $this->assetPublisherMock->expects($this->once())
            ->method('publish')
            ->with($assetMock);

        $assetMock->expects($this->once())
            ->method('getFilePath')
            ->willReturn(self::FILE_TEST_VALUE);

        $this->sourceThemeDeployCommand->run($this->getInputMock(), $outputMock);
    }

    /**
     * Run test for execute method with incorrect theme value
     */
    public function testExecuteIncorrectThemeFormat(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Value "theme-value" of the option "theme" has invalid format. The format should be'
        );
        /** @var OutputInterface|MockObject $outputMock */
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with(self::LOCALE_TEST_VALUE)
            ->willReturn(true);

        $valueMap = [
            ['area', self::AREA_TEST_VALUE],
            ['locale', self::LOCALE_TEST_VALUE],
            ['theme', self::THEME_INCORRECT_FORMAT_VALUE],
            ['type', self::TYPE_TEST_VALUE]
        ];

        $this->sourceThemeDeployCommand->run(
            $this->getInputMock($valueMap),
            $outputMock
        );
    }

    /**
     * Run test for execute method with non existing theme
     */
    public function testExecuteNonExistingValue(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Verify entered values of the argument and options.');
        /** @var OutputInterface|MockObject $outputMock */
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $assetMock = $this->getMockBuilder(LocalInterface::class)
            ->getMockForAbstractClass();

        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with(self::LOCALE_TEST_VALUE)
            ->willReturn(true);

        $this->assetRepositoryMock->expects($this->once())
            ->method('createAsset')
            ->with(
                'file-test-value/test' . DIRECTORY_SEPARATOR . 'file' . '.' . self::TYPE_TEST_VALUE,
                [
                    'area' => self::AREA_TEST_VALUE,
                    'theme' => self::THEME_NONEXISTING_VALUE,
                    'locale' => self::LOCALE_TEST_VALUE
                ]
            )->willReturn($assetMock);

        $this->assetPublisherMock->expects($this->once())
            ->method('publish')
            ->with($assetMock)
            ->willThrowException(new NotFoundException());

        $valueMap = [
            ['area', self::AREA_TEST_VALUE],
            ['locale', self::LOCALE_TEST_VALUE],
            ['theme', self::THEME_NONEXISTING_VALUE],
            ['type', self::TYPE_TEST_VALUE]
        ];

        $this->sourceThemeDeployCommand->run(
            $this->getInputMock($valueMap),
            $outputMock
        );
    }

    /**
     * @return InputInterface|MockObject
     */
    private function getInputMock(array $valueMap = []): MockObject
    {
        $inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();

        $defaultValueMap = [
            ['area', self::AREA_TEST_VALUE],
            ['locale', self::LOCALE_TEST_VALUE],
            ['theme', self::THEME_TEST_VALUE],
            ['type', self::TYPE_TEST_VALUE]
        ];
        $valueMap = empty($valueMap) ? $defaultValueMap : $valueMap;

        $inputMock->expects($this->exactly(4))
            ->method('getOption')
            ->willReturnMap(
                $valueMap
            );
        $inputMock->expects($this->once())
            ->method('getArgument')
            ->with('file')
            ->willReturn([self::FILE_TEST_VALUE]);

        return $inputMock;
    }
}
