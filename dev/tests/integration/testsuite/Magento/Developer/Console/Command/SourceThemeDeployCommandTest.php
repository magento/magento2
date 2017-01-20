<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Console\Command;

use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SourceThemeDeployCommandTest
 *
 * @see \Magento\Developer\Console\Command\SourceThemeDeployCommand
 */
class SourceThemeDeployCommandTest extends \PHPUnit_Framework_TestCase
{
    const PUB_STATIC_DIRECTORY = 'pub/static';

    const AREA_TEST_VALUE = 'frontend';

    const LOCALE_TEST_VALUE = 'en_US';

    const THEME_TEST_VALUE = 'Magento/luma';

    const TYPE_TEST_VALUE = 'less';

    /**
     * @var SourceThemeDeployCommand
     */
    private $command;

    /**
     * @var string
     */
    private $pubStatic;

    /**
     * @var array
     */
    private $compiledFiles = ['css/styles-m', 'css/styles-l'];

    /**
     * Set up
     */
    protected function setUp()
    {
        global $installDir;

        $this->pubStatic = $installDir . DIRECTORY_SEPARATOR . self::PUB_STATIC_DIRECTORY;
        $this->command = Bootstrap::getObjectManager()->get(SourceThemeDeployCommand::class);
    }

    /**
     * Run test for execute method
     */
    public function testExecute()
    {
        $error = [];

        /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject $outputMock */
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();

        $this->clearStaticDirectory();

        $this->command->run($this->getInputMock(), $outputMock);

        /** @var \SplFileInfo $file */
        foreach ($this->collectFiles($this->pubStatic) as $file) {
            $fileInfo = pathinfo($file->getFilename());
            if (!in_array('css/' . $fileInfo['filename'], $this->compiledFiles, true)
                && !$file->isLink()
            ) {
                $error[] = 'Bad file -> ' . $file->getFilename() . PHP_EOL;
            }
        }

        $this->clearStaticDirectory();

        self::assertEmpty($error, implode($error));
    }

    /**
     * @return void
     */
    private function clearStaticDirectory()
    {
        /** @var \SplFileInfo $file */
        foreach ($this->collectFiles($this->pubStatic) as $file) {
            @unlink($file->getPathname());
        }
    }

    /**
     * @param string $path
     * @return \RegexIterator
     */
    private function collectFiles($path)
    {
        $flags = \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, $flags));

        return new \RegexIterator(
            $iterator,
            '#\.less$#',
            \RegexIterator::MATCH,
            \RegexIterator::USE_KEY
        );
    }

    /**
     * @return InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getInputMock()
    {
        $inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();

        $inputMock->expects(self::exactly(4))
            ->method('getOption')
            ->willReturnMap(
                [
                    ['area', self::AREA_TEST_VALUE],
                    ['locale', self::LOCALE_TEST_VALUE],
                    ['theme', self::THEME_TEST_VALUE],
                    ['type', self::TYPE_TEST_VALUE]
                ]
            );
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('file')
            ->willReturn($this->compiledFiles);

        return $inputMock;
    }
}
