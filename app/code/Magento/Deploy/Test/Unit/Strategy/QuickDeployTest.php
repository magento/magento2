<?php
/**
 * Copyright 2024 Adobe
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
    private QuickDeploy $strategy;

    /** @var PackagePool|MockObject */
    private MockObject $packagePool;

    /** @var Queue|MockObject */
    private MockObject $queue;

    protected function setUp(): void
    {
        $this->packagePool = $this->createMock(PackagePool::class);
        $this->queue = $this->createMock(Queue::class);
        $this->strategy = new QuickDeploy($this->packagePool, $this->queue);
    }

    /**
     * When all packages are being deployed (no theme filtering), a package whose parent is also in
     * the queue must declare that parent as a queue dependency so parallel workers don't race.
     */
    public function testParentInQueueIsPassedAsDependency(): void
    {
        $parentPath = 'frontend/Magento/blank/en_GB';
        $childPath  = 'frontend/Magento/blank/en_US';

        $parent = $this->makePackage('frontend', 'Magento/blank', 'en_GB', $parentPath, 1, null);
        $child  = $this->makePackage('frontend', 'Magento/blank', 'en_US', $childPath, 2, $parent, [$parent]);

        $this->packagePool->method('getPackagesForDeployment')->willReturn([$parent, $child]);

        $calls = [];
        $this->queue->method('add')->willReturnCallback(function ($pkg, $deps) use (&$calls) {
            $calls[] = [$pkg, $deps];
        });
        $this->queue->expects($this->exactly(2))->method('add');
        $this->queue->expects($this->once())->method('process');

        $options = [
            Options::NO_PARENT    => false,
            Options::THEME        => [],
            Options::EXCLUDE_THEME => [],
        ];
        $this->strategy->deploy($options);

        // Parent added first with no dependency
        $this->assertSame($parent, $calls[0][0]);
        $this->assertSame([], $calls[0][1]);

        // Child added with parent as explicit queue dependency
        $this->assertSame($child, $calls[1][0]);
        $this->assertSame([$parentPath => $parent], $calls[1][1]);
    }

    /**
     * When a theme-hierarchy parent (e.g. blank as parent of luma) is filtered out by PackagePool
     * (e.g. because --theme Magento/luma was passed), it is never in groupedPackages and therefore
     * never in queuedPaths. Passing it as a queue dependency would cause Queue::process() to wait
     * forever for a package that will never be deployed (deadlock). The child must be queued with
     * no dependencies in that case.
     *
     * This matches real-world behaviour: PackagePool::getPackagesForDeployment() filters by theme,
     * so blank is absent from the returned list even though luma's theme-hierarchy parent IS blank.
     * preparePackages() still calls setParent() on luma pointing at the blank Package object (via
     * getParentPackages()), but that object is not in queuedPaths.
     */
    public function testParentNotInQueueDueToThemeFilterIsNotPassedAsDependency(): void
    {
        $blankPath = 'frontend/Magento/blank/en_GB';
        $lumaPath  = 'frontend/Magento/luma/en_GB';

        // blank is a theme-hierarchy Package known to luma via getParentPackages(), but it is NOT
        // returned by PackagePool (filtered by --theme Magento/luma) — so not in groupedPackages.
        $blank = $this->makePackage('frontend', 'Magento/blank', 'en_GB', $blankPath, 1, null);

        // luma: level 2 in the theme hierarchy (inherits from blank). getParent() returns blank
        // because preparePackages() would call setParent(blank) via the getParentPackages() path.
        $luma = $this->makePackage('frontend', 'Magento/luma', 'en_GB', $lumaPath, 2, $blank, [$blank]);

        // Pool returns ONLY luma — blank is absent (filtered by PackagePool for --theme Magento/luma)
        $this->packagePool->method('getPackagesForDeployment')->willReturn([$luma]);

        $calls = [];
        $this->queue->method('add')->willReturnCallback(function ($pkg, $deps) use (&$calls) {
            $calls[] = [$pkg, $deps];
        });

        $options = [
            Options::NO_PARENT    => true,
            Options::THEME        => ['Magento/luma'],
            Options::EXCLUDE_THEME => [],
        ];
        $this->strategy->deploy($options);

        // Only luma should be queued
        $this->assertCount(1, $calls);
        $this->assertSame($luma, $calls[0][0]);
        // blank is not in queuedPaths → no dependency, no deadlock
        $this->assertSame([], $calls[0][1]);
    }

    public function testVirtualPackagesAreSkipped(): void
    {
        $virtual = $this->makePackage('frontend', 'Magento/blank', 'en_GB', 'v/path', 1, null, [], true);

        $this->packagePool->method('getPackagesForDeployment')->willReturn([$virtual]);

        // Nothing should be added to the queue
        $this->queue->expects($this->never())->method('add');
        $this->queue->expects($this->once())->method('process');

        $options = [
            Options::NO_PARENT    => false,
            Options::THEME        => [],
            Options::EXCLUDE_THEME => [],
        ];
        $this->strategy->deploy($options);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @param string $path
     * @param int $inheritanceLevel Used by getInheritanceLevel() to assign groupedPackages level
     * @param Package|null $parent  Returned by getParent() — set as if preparePackages already ran
     * @param Package[] $parentPackages Returned by getParentPackages()
     * @param bool $virtual
     */
    private function makePackage(
        string $area,
        string $theme,
        string $locale,
        string $path,
        int $inheritanceLevel = 1,
        ?Package $parent = null,
        array $parentPackages = [],
        bool $virtual = false
    ): MockObject {
        $pkg = $this->createMock(Package::class);
        $pkg->method('isVirtual')->willReturn($virtual);
        $pkg->method('getArea')->willReturn($area);
        $pkg->method('getTheme')->willReturn($theme);
        $pkg->method('getLocale')->willReturn($locale);
        $pkg->method('getPath')->willReturn($path);
        $pkg->method('getInheritanceLevel')->willReturn($inheritanceLevel);
        $pkg->method('getParent')->willReturn($parent);
        $pkg->method('getParentPackages')->willReturn($parentPackages);
        $pkg->method('aggregate')->willReturn(null);
        return $pkg;
    }
}
