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
use Magento\Deploy\Strategy\StandardDeploy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see StandardDeploy
 */
class StandardDeployTest extends TestCase
{
    private StandardDeploy $strategy;

    /** @var PackagePool|MockObject */
    private MockObject $packagePool;

    /** @var Queue|MockObject */
    private MockObject $queue;

    private array $defaultOptions;

    protected function setUp(): void
    {
        $this->packagePool = $this->createMock(PackagePool::class);
        $this->queue = $this->createMock(Queue::class);
        $this->strategy = new StandardDeploy($this->packagePool, $this->queue);
        $this->defaultOptions = [
            Options::NO_PARENT    => false,
            Options::THEME        => [],
            Options::EXCLUDE_THEME => [],
        ];
    }

    public function testVirtualPackagesAreSkipped(): void
    {
        $virtual = $this->makePackage('frontend', 'Magento/blank', 'en_GB', 'path/v', true);
        $real    = $this->makePackage('frontend', 'Magento/blank', 'en_US', 'path/r', false);

        $this->packagePool->method('getPackagesForDeployment')->willReturn([$virtual, $real]);

        // Only the real package should be added
        $this->queue->expects($this->once())->method('add')->with($real, $this->anything());
        $this->queue->expects($this->once())->method('process');

        $result = $this->strategy->deploy($this->defaultOptions);

        $this->assertNotContains($virtual, $result);
        $this->assertContains($real, $result);
    }

    public function testFirstLocalePerThemeHasNoParentAndNoQueueDependency(): void
    {
        $base = $this->makePackage('frontend', 'Magento/blank', 'en_GB', 'frontend/Magento/blank/en_GB');

        $this->packagePool->method('getPackagesForDeployment')->willReturn([$base]);

        // Base locale should be queued with empty dependencies
        $this->queue->expects($this->once())->method('add')->with($base, []);
        $this->queue->expects($this->once())->method('process');

        $this->strategy->deploy($this->defaultOptions);
    }

    public function testSecondLocalePerThemeGetsParentSetAndQueuedWithDependency(): void
    {
        $basePath    = 'frontend/Magento/blank/en_GB';
        $variantPath = 'frontend/Magento/blank/en_US';

        $base    = $this->makePackage('frontend', 'Magento/blank', 'en_GB', $basePath);
        $variant = $this->makePackage('frontend', 'Magento/blank', 'en_US', $variantPath);

        $this->packagePool->method('getPackagesForDeployment')->willReturn([$base, $variant]);

        // Variant should have setParent() called with the base package
        $variant->expects($this->once())->method('setParent')->with($base);

        // Variant's getParent() returns base (as set by setParent)
        $variant->method('getParent')->willReturn($base);
        $base->method('getParent')->willReturn(null);

        // Base queued with no dependency; variant queued with base as dependency
        $calls = [];
        $this->queue->method('add')->willReturnCallback(function ($pkg, $deps) use (&$calls) {
            $calls[] = [$pkg, $deps];
        });
        $this->queue->expects($this->exactly(2))->method('add');
        $this->queue->expects($this->once())->method('process');

        $this->strategy->deploy($this->defaultOptions);

        // Base is first with no dependencies
        $this->assertSame($base, $calls[0][0]);
        $this->assertSame([], $calls[0][1]);

        // Variant is second with base as dependency
        $this->assertSame($variant, $calls[1][0]);
        $this->assertSame([$basePath => $base], $calls[1][1]);
    }

    public function testBasePackagesQueuedBeforeVariants(): void
    {
        $base1    = $this->makePackage('frontend', 'Magento/blank', 'en_GB', 'frontend/Magento/blank/en_GB');
        $variant1 = $this->makePackage('frontend', 'Magento/blank', 'en_US', 'frontend/Magento/blank/en_US');
        $base2    = $this->makePackage('frontend', 'Magento/luma', 'en_GB', 'frontend/Magento/luma/en_GB');
        $variant2 = $this->makePackage('frontend', 'Magento/luma', 'en_US', 'frontend/Magento/luma/en_US');

        // Pool returns interleaved order: base1, base2, variant1, variant2
        $this->packagePool->method('getPackagesForDeployment')
            ->willReturn([$base1, $base2, $variant1, $variant2]);

        $variant1->method('setParent')->with($base1);
        $variant1->method('getParent')->willReturn($base1);
        $base1->method('getParent')->willReturn(null);

        $variant2->method('setParent')->with($base2);
        $variant2->method('getParent')->willReturn($base2);
        $base2->method('getParent')->willReturn(null);

        $order = [];
        $this->queue->method('add')->willReturnCallback(function ($pkg) use (&$order) {
            $order[] = $pkg;
        });
        $this->queue->expects($this->exactly(4))->method('add');

        $this->strategy->deploy($this->defaultOptions);

        // All base packages appear before any variant
        $this->assertSame($base1, $order[0]);
        $this->assertSame($base2, $order[1]);
        $this->assertSame($variant1, $order[2]);
        $this->assertSame($variant2, $order[3]);
    }

    public function testDifferentThemesHaveSeparateBaseLocales(): void
    {
        // blank/en_GB and luma/en_GB are both base locales (different themes)
        $blankBase    = $this->makePackage('frontend', 'Magento/blank', 'en_GB', 'frontend/Magento/blank/en_GB');
        $blankVariant = $this->makePackage('frontend', 'Magento/blank', 'en_US', 'frontend/Magento/blank/en_US');
        $lumaBase     = $this->makePackage('frontend', 'Magento/luma', 'en_GB', 'frontend/Magento/luma/en_GB');

        $this->packagePool->method('getPackagesForDeployment')
            ->willReturn([$blankBase, $blankVariant, $lumaBase]);

        $blankVariant->method('setParent')->with($blankBase);
        $blankVariant->method('getParent')->willReturn($blankBase);
        $blankBase->method('getParent')->willReturn(null);
        // lumaBase is first for its theme, so no setParent expected
        $lumaBase->expects($this->never())->method('setParent');
        $lumaBase->method('getParent')->willReturn(null);

        $this->queue->expects($this->exactly(3))->method('add');

        $this->strategy->deploy($this->defaultOptions);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makePackage(
        string $area,
        string $theme,
        string $locale,
        string $path,
        bool $virtual = false
    ): MockObject {
        $pkg = $this->createMock(Package::class);
        $pkg->method('isVirtual')->willReturn($virtual);
        $pkg->method('getArea')->willReturn($area);
        $pkg->method('getTheme')->willReturn($theme);
        $pkg->method('getLocale')->willReturn($locale);
        $pkg->method('getPath')->willReturn($path);
        $pkg->method('aggregate')->willReturn(null);
        return $pkg;
    }
}
