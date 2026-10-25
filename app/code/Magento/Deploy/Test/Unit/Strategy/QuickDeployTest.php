<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Strategy;

use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Process\Queue;
use Magento\Deploy\Strategy\QuickDeploy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see QuickDeploy
 */
class QuickDeployTest extends TestCase
{
    /**
     * @var PackagePool|MockObject
     */
    private $packagePool;

    /**
     * @var Queue|MockObject
     */
    private $queue;

    /**
     * Packages and dependencies in the order they were added to the queue
     *
     * @var array
     */
    private $queued = [];

    protected function setUp(): void
    {
        $this->packagePool = $this->createStub(PackagePool::class);
        $this->queue = $this->createMock(Queue::class);
        $this->queue->method('add')->willReturnCallback(
            function (Package $package, array $dependencies = []) {
                $this->queued[] = [$package, $dependencies];
                return true;
            }
        );
        $this->queue->expects($this->once())->method('process');
    }

    public function testSecondLocaleDependsOnBaseLocaleOfSameTheme(): void
    {
        $baseLocale = $this->createPackage('Magento/blank', 'en_US', 1);
        $secondLocale = $this->createPackage('Magento/blank', 'de_DE', 1);
        $this->expectParent($baseLocale, null);
        $this->expectParent($secondLocale, $baseLocale);

        $this->assertSame([$baseLocale, $secondLocale], $this->deploy([$baseLocale, $secondLocale]));
        $this->assertQueued([
            [$baseLocale, []],
            [$secondLocale, ['frontend/Magento/blank/en_US' => $baseLocale]],
        ]);
    }

    public function testChildThemeDependsOnClosestNonVirtualAncestor(): void
    {
        $blank = $this->createPackage('Magento/blank', 'en_US', 1);
        $lumaTheme = $this->createPackage('Magento/luma', Package::BASE_LOCALE, 2, true);
        $luma = $this->createPackage('Magento/luma', 'en_US', 2, false, [$blank, $lumaTheme]);
        $this->expectParent($blank, null);
        $this->expectParent($lumaTheme, null);
        $this->expectParent($luma, $blank);

        $this->deploy([$blank, $luma, $lumaTheme]);
        $this->assertQueued([
            [$blank, []],
            [$luma, ['frontend/Magento/blank/en_US' => $blank]],
        ]);
    }

    public function testParentNotDeployedInThisRunIsNotUsed(): void
    {
        $blank = $this->createPackage('Magento/blank', 'en_US', 1);
        $luma = $this->createPackage('Magento/luma', 'en_US', 2, false, [$blank]);
        $this->expectParent($blank, null);
        $this->expectParent($luma, null);

        $deployed = $this->deploy(
            [$blank, $luma],
            [Options::NO_PARENT => true, Options::THEME => ['Magento/luma'], Options::EXCLUDE_THEME => ['none']]
        );

        $this->assertSame([$luma], $deployed);
        $this->assertQueued([[$luma, []]]);
    }

    public function testVirtualPackagesAreSkipped(): void
    {
        $virtual = $this->createPackage('Magento/blank', Package::BASE_LOCALE, 1, true);
        $this->expectParent($virtual, null);

        $this->assertSame([], $this->deploy([$virtual]));
        $this->assertQueued([]);
    }

    public function testPackagesAreQueuedInInheritanceOrder(): void
    {
        $blank = $this->createPackage('Magento/blank', 'en_US', 1);
        $luma = $this->createPackage('Magento/luma', 'en_US', 2, false, [$blank]);
        $lumaSecondLocale = $this->createPackage('Magento/luma', 'de_DE', 2, false, [$blank]);
        $this->expectParent($blank, null);
        $this->expectParent($luma, $blank);
        $this->expectParent($lumaSecondLocale, $luma);

        $this->assertSame([$blank, $luma, $lumaSecondLocale], $this->deploy([$luma, $lumaSecondLocale, $blank]));
        $this->assertQueued([
            [$blank, []],
            [$luma, ['frontend/Magento/blank/en_US' => $blank]],
            [$lumaSecondLocale, ['frontend/Magento/luma/en_US' => $luma]],
        ]);
    }

    /**
     * @param Package[] $packages
     * @param array $options
     * @return Package[]
     */
    private function deploy(array $packages, array $options = []): array
    {
        $this->packagePool->method('getPackagesForDeployment')->willReturn($packages);
        $strategy = new QuickDeploy($this->packagePool, $this->queue);
        return $strategy->deploy(
            $options + [Options::NO_PARENT => false, Options::THEME => ['all'], Options::EXCLUDE_THEME => ['none']]
        );
    }

    /**
     * Expect the parent to be set, and copying from it to be enabled, only when there is one
     *
     * @param MockObject $package
     * @param Package|null $parent
     * @return void
     */
    private function expectParent(MockObject $package, ?Package $parent): void
    {
        $package->expects($parent ? $this->once() : $this->never())->method('setParent')->with($parent);
        $package->expects($parent ? $this->once() : $this->never())
            ->method('setParam')
            ->with('copy_from_parent', true);
    }

    /**
     * @param array $expected
     * @return void
     */
    private function assertQueued(array $expected): void
    {
        $this->assertSame($expected, $this->queued);
    }

    /**
     * Create a package, virtual packages are never aggregated
     *
     * @param string $theme
     * @param string $locale
     * @param int $inheritanceLevel
     * @param bool $isVirtual
     * @param Package[] $parentPackages closest ancestor last
     * @return Package|MockObject
     */
    private function createPackage(
        string $theme,
        string $locale,
        int $inheritanceLevel,
        bool $isVirtual = false,
        array $parentPackages = []
    ): MockObject {
        $package = $this->createMock(Package::class);
        $package->method('getArea')->willReturn('frontend');
        $package->method('getTheme')->willReturn($theme);
        $package->method('getLocale')->willReturn($locale);
        $package->method('getPath')->willReturn('frontend/' . $theme . '/' . $locale);
        $package->method('isVirtual')->willReturn($isVirtual);
        $package->method('getInheritanceLevel')->willReturn($inheritanceLevel);
        $package->method('getParentPackages')->willReturn($parentPackages);
        $package->expects($isVirtual ? $this->never() : $this->once())->method('aggregate');
        return $package;
    }
}
