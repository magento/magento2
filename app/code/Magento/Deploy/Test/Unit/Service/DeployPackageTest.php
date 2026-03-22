<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Service\DeployPackage;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\App\State as AppState;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

/**
 * @see DeployPackage
 */
class DeployPackageTest extends TestCase
{
    private DeployPackage $service;

    /** @var Package|MockObject */
    private MockObject $package;

    /** @var Package|MockObject */
    private MockObject $parentPackage;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->service = $objectManager->getObject(DeployPackage::class, [
            'appState'       => $this->createMock(AppState::class),
            'localeResolver' => $this->createMock(LocaleResolver::class),
            'deployStaticFile' => $this->createMock(DeployStaticFile::class),
            'logger'         => $this->createMock(LoggerInterface::class),
        ]);

        $this->package = $this->createMock(Package::class);
        $this->package->method('getArea')->willReturn('frontend');
        $this->package->method('getTheme')->willReturn('Magento/blank');
        $this->package->method('getLocale')->willReturn('en_US');

        $this->parentPackage = $this->createMock(Package::class);
        $this->parentPackage->method('getArea')->willReturn('frontend');
        $this->parentPackage->method('getTheme')->willReturn('Magento/blank');
        $this->parentPackage->method('getLocale')->willReturn('en_GB');
    }

    // -------------------------------------------------------------------------
    // shouldBulkCopy
    // -------------------------------------------------------------------------

    public function testShouldBulkCopyReturnsFalseForEmptyPackage(): void
    {
        $this->package->method('getFiles')->willReturn([]);
        $this->assertFalse($this->invokeShouldBulkCopy($this->package, $this->parentPackage));
    }

    public function testShouldBulkCopyReturnsFalseWhenFewerThanHalfInherited(): void
    {
        $parentFiles = [];

        // 2 own files (not in parent)
        $own1 = $this->makeFile('a.css', $this->package, 'frontend', 'Magento/blank', 'en_US');
        $own2 = $this->makeFile('b.css', $this->package, 'frontend', 'Magento/blank', 'en_US');

        // 1 inherited file (in parent, scope mismatch)
        $inherited = $this->makeFile('c.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB');
        $parentFiles['c.css'] = $inherited;

        $this->parentPackage->method('getFiles')->willReturn($parentFiles);
        $this->package->method('getFiles')->willReturn([$own1, $own2, $inherited]);

        $this->assertFalse($this->invokeShouldBulkCopy($this->package, $this->parentPackage));
    }

    public function testShouldBulkCopyReturnsTrueWhenMajorityInherited(): void
    {
        $parentFiles = [];

        // 1 own file
        $own = $this->makeFile('a.css', $this->package, 'frontend', 'Magento/blank', 'en_US');

        // 3 inherited files
        $parentFiles['b.css'] = $this->makeFile('b.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB');
        $parentFiles['c.css'] = $this->makeFile('c.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB');
        $parentFiles['d.css'] = $this->makeFile('d.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB');

        $this->parentPackage->method('getFiles')->willReturn($parentFiles);
        $this->package->method('getFiles')->willReturn(
            array_merge([$own], array_values($parentFiles))
        );

        $this->assertTrue($this->invokeShouldBulkCopy($this->package, $this->parentPackage));
    }

    public function testShouldBulkCopyExcludesContentFilesFromInheritedCount(): void
    {
        // Content files (pre-set content, e.g. inline CSS) are never bulk-copied and
        // should not count toward the inherited ratio.
        $parentFiles = [];

        $contentFile = $this->makeFile(
            'inline.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB', 'body{}'
        );
        $parentFiles['inline.css'] = $contentFile;

        $inherited = $this->makeFile('b.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB');
        $parentFiles['b.css'] = $inherited;

        $own = $this->makeFile('c.css', $this->package, 'frontend', 'Magento/blank', 'en_US');

        $this->parentPackage->method('getFiles')->willReturn($parentFiles);
        // 1 content file + 1 inherited + 1 own = 3 total; only 1 counts as inherited (33%)
        $this->package->method('getFiles')->willReturn([$contentFile, $inherited, $own]);

        $this->assertFalse($this->invokeShouldBulkCopy($this->package, $this->parentPackage));
    }

    // -------------------------------------------------------------------------
    // isInheritedFile
    // -------------------------------------------------------------------------

    public function testIsInheritedFileReturnsFalseWithNoParent(): void
    {
        $file = $this->makeFile('a.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB');
        $this->assertFalse($this->invokeIsInheritedFile($file, $this->package, null));
    }

    public function testIsInheritedFileReturnsFalseWhenFileOriginatesFromPackage(): void
    {
        // File originated from this package — should never be skipped
        $file = $this->makeFile('override.css', $this->package, 'frontend', 'Magento/blank', 'en_US');
        $this->parentPackage->method('getFiles')->willReturn(['override.css' => $file]);

        $this->assertFalse($this->invokeIsInheritedFile($file, $this->package, $this->parentPackage));
    }

    public function testIsInheritedFileReturnsFalseWhenNotInParentFileList(): void
    {
        // Scope mismatch but the parent did not deploy this file (e.g. theme-specific critical.css)
        $file = $this->makeFile('critical.css', $this->parentPackage, 'frontend', 'Magento/luma', 'en_GB');
        $this->parentPackage->method('getFiles')->willReturn([]); // not in parent

        $this->assertFalse($this->invokeIsInheritedFile($file, $this->package, $this->parentPackage));
    }

    public function testIsInheritedFileReturnsTrueForScopeMismatchPresentInParent(): void
    {
        $file = $this->makeFile('styles.css', $this->parentPackage, 'frontend', 'Magento/blank', 'en_GB');
        $this->parentPackage->method('getFiles')->willReturn(['styles.css' => $file]);

        $this->assertTrue($this->invokeIsInheritedFile($file, $this->package, $this->parentPackage));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeFile(
        string $fileId,
        Package $origPackage,
        string $area,
        string $theme,
        string $locale,
        ?string $content = null
    ): MockObject {
        $file = $this->createMock(PackageFile::class);
        $file->method('getFileId')->willReturn($fileId);
        $file->method('getDeployedFileId')->willReturn($fileId);
        $file->method('getOrigPackage')->willReturn($origPackage);
        $file->method('getArea')->willReturn($area);
        $file->method('getTheme')->willReturn($theme);
        $file->method('getLocale')->willReturn($locale);
        $file->method('getContent')->willReturn($content);
        return $file;
    }

    private function invokeShouldBulkCopy(Package $package, Package $parent): bool
    {
        $method = new ReflectionMethod(DeployPackage::class, 'shouldBulkCopy');
        $method->setAccessible(true);
        return $method->invoke($this->service, $package, $parent);
    }

    private function invokeIsInheritedFile(PackageFile $file, Package $package, ?Package $parent): bool
    {
        $method = new ReflectionMethod(DeployPackage::class, 'isInheritedFile');
        $method->setAccessible(true);
        return $method->invoke($this->service, $file, $package, $parent);
    }
}
