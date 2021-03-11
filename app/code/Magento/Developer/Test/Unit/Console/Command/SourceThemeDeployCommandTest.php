<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Framework\Validator\Locale;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\View\Asset\LocalInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Developer\Console\Command\SourceThemeDeployCommand;

/**
 * Class SourceThemeDeployCommandTest
 *
 * @see \Magento\Developer\Console\Command\SourceThemeDeployCommand
 */
class SourceThemeDeployCommandTest extends \PHPUnit\Framework\TestCase
{
    const AREA_TEST_VALUE = 'area-test-value';

    const LOCALE_TEST_VALUE = 'locale-test-value';

    const THEME_TEST_VALUE = 'Vendor/theme';

    const THEME_INCORRECT_FORMAT_VALUE = 'theme-value';

    const THEME_NONEXISTING_VALUE = 'NonExistentVendor/theme';

    const TYPE_TEST_VALUE = 'type-test-value';

    const FILE_TEST_VALUE = 'file-test-value/test/file';

    /**
     * @var SourceThemeDeployCommand
     */
    private $sourceThemeDeployCommand;

    /**
     * @var Locale|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validatorMock;

    /**
     * @var Publisher|\PHPUnit\Framework\MockObject\MockObject
     */
    private $assetPublisherMock;

    /**
     * @var Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $assetRepositoryMock;

    /**
     * Set up
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
     */
    public function testExecute()
    {
        /** @var OutputInterface|\PHPUnit\Framework\MockObject\MockObject $outputMock */
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $assetMock = $this->getMockBuilder(LocalInterface::class)
            ->getMockForAbstractClass();

        $this->validatorMock->expects(self::once())
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

        $outputMock->expects(self::at(0))
            ->method('writeln')
            ->with($message);
        $outputMock->expects(self::at(1))
            ->method('writeln')
            ->with('<comment>-> file-test-value/test/file</comment>');
        $outputMock->expects(self::at(2))
            ->method('writeln')
            ->with('<info>Successfully processed.</info>');

        $this->assetRepositoryMock->expects(self::once())
            ->method('createAsset')
            ->with(
                'file-test-value/test' . DIRECTORY_SEPARATOR . 'file' . '.' . self::TYPE_TEST_VALUE,
                [
                    'area' => self::AREA_TEST_VALUE,
                    'theme' => self::THEME_TEST_VALUE,
                    'locale' => self::LOCALE_TEST_VALUE,
                ]
            )->willReturn($assetMock);

        $this->assetPublisherMock->expects(self::once())
            ->method('publish')
            ->with($assetMock);

        $assetMock->expects(self::once())
            ->method('getFilePath')
            ->willReturn(self::FILE_TEST_VALUE);

        $this->sourceThemeDeployCommand->run($this->getInputMock(), $outputMock);
    }

    /**
     * Run test for execute method with incorrect theme value
     *
     */
    public function testExecuteIncorrectThemeFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "theme-value" of the option "theme" has invalid format. The format should be');

        /** @var OutputInterface|\PHPUnit\Framework\MockObject\MockObject $outputMock */
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->validatorMock->expects(self::once())
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
     *
     */
    public function testExecuteNonExistingValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Verify entered values of the argument and options.');

        /** @var OutputInterface|\PHPUnit\Framework\MockObject\MockObject $outputMock */
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $assetMock = $this->getMockBuilder(LocalInterface::class)
            ->getMockForAbstractClass();

        $this->validatorMock->expects(self::once())
            ->method('isValid')
            ->with(self::LOCALE_TEST_VALUE)
            ->willReturn(true);

        $this->assetRepositoryMock->expects(self::once())
            ->method('createAsset')
            ->with(
                'file-test-value/test' . DIRECTORY_SEPARATOR . 'file' . '.' . self::TYPE_TEST_VALUE,
                [
                    'area' => self::AREA_TEST_VALUE,
                    'theme' => self::THEME_NONEXISTING_VALUE,
                    'locale' => self::LOCALE_TEST_VALUE,
                ]
            )->willReturn($assetMock);

        $this->assetPublisherMock->expects(self::once())
            ->method('publish')
            ->with($assetMock)
            ->willThrowException(new \Magento\Framework\View\Asset\File\NotFoundException);

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
     * @return InputInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getInputMock(array $valueMap = [])
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

        $inputMock->expects(self::exactly(4))
            ->method('getOption')
            ->willReturnMap(
                $valueMap
            );
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('file')
            ->willReturn([self::FILE_TEST_VALUE]);

        return $inputMock;
    }
}
