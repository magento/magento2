<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Composer\Test\Unit;

use Composer\Console\Application;
use Composer\Console\ApplicationFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\DependencyChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DependencyCheckerTest extends TestCase
{

    /**
     * @var ApplicationFactory|MockObject
     */
    private ApplicationFactory $composerFactory;

    /**
     * @var Application|MockObject
     */
    private Application $composerApp;

    protected function setUp(): void
    {
        $this->composerFactory = $this->getMockBuilder(ApplicationFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->composerApp = $this->getMockBuilder(Application::class)
            ->setMethods(['setAutoExit', 'resetComposer', 'run','__destruct'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->composerFactory->method('create')->willReturn($this->composerApp);
        parent::setUp();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testCheckDependencies(): void
    {

        $directoryList = $this->createMock(DirectoryList::class);
        $directoryList->expects($this->exactly(2))->method('getRoot');
        $this->composerApp->expects($this->once())->method('setAutoExit')->with(false);
        $this->composerApp->expects($this->any())->method('__destruct');

        $this->composerApp
            ->method('run')
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(
                    function ($input, $buffer) {
                        $output = 'magento/package-b requires magento/package-a (1.0)' . PHP_EOL .
                            'magento/project-community-edition requires magento/package-a (1.0)' . PHP_EOL .
                            'magento/package-c requires magento/package-a (1.0)' . PHP_EOL;
                        $buffer->writeln($output);
                        return 1;
                    }
                ),
                $this->returnCallback(
                    function ($input, $buffer) {
                        $output = 'magento/package-c requires magento/package-b (1.0)' . PHP_EOL .
                            'magento/project-community-edition requires magento/package-a (1.0)' . PHP_EOL .
                            'magento/package-d requires magento/package-b (1.0)' . PHP_EOL;
                        $buffer->writeln($output);
                        return 1;
                    }
                )
            );

        $dependencyChecker = new DependencyChecker($this->composerFactory, $directoryList);
        $expected = [
            'magento/package-a' => ['magento/package-b', 'magento/package-c'],
            'magento/package-b' => ['magento/package-c', 'magento/package-d'],
        ];
        $this->assertEquals(
            $expected,
            $dependencyChecker->checkDependencies(['magento/package-a', 'magento/package-b'])
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testCheckDependenciesExcludeSelf(): void
    {
        $directoryList = $this->createMock(DirectoryList::class);
        $directoryList->expects($this->exactly(3))->method('getRoot');
        $this->composerApp->expects($this->once())->method('setAutoExit')->with(false);
        $this->composerApp->expects($this->any())->method('__destruct');

        $this->composerApp
            ->method('run')
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(
                    function ($input, $buffer) {
                        $output = 'magento/package-b requires magento/package-a (1.0)' . PHP_EOL .
                            'magento/project-community-edition requires magento/package-a (1.0)' . PHP_EOL .
                            'magento/package-c requires magento/package-a (1.0)' . PHP_EOL;
                        $buffer->writeln($output);
                        return 1;
                    }
                ),
                $this->returnCallback(
                    function ($input, $buffer) {
                        $output = 'magento/package-c requires magento/package-b (1.0)' . PHP_EOL .
                            'magento/project-community-edition requires magento/package-a (1.0)' . PHP_EOL .
                            'magento/package-d requires magento/package-b (1.0)' . PHP_EOL;
                        $buffer->writeln($output);
                        return 1;
                    }
                ),
                $this->returnCallback(
                    function ($input, $buffer) {
                        $output = 'magento/package-d requires magento/package-c (1.0)' . PHP_EOL .
                            'magento/project-community-edition requires magento/package-a (1.0)' . PHP_EOL;
                        $buffer->writeln($output);
                        return 1;
                    }
                )
            );

        $dependencyChecker = new DependencyChecker($this->composerFactory, $directoryList);
        $expected = [
            'magento/package-a' => [],
            'magento/package-b' => ['magento/package-d'],
            'magento/package-c' => ['magento/package-d']
        ];
        $this->assertEquals(
            $expected,
            $dependencyChecker->checkDependencies(
                ['magento/package-a', 'magento/package-b', 'magento/package-c'],
                true
            )
        );
    }
}
