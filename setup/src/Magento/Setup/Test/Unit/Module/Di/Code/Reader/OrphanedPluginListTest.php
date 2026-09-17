<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Setup\Module\Di\Code\Reader\OrphanedPluginList;
use PHPUnit\Framework\TestCase;

class OrphanedPluginListTest extends TestCase
{
    private const EXISTING_TARGET = \DateTime::class;
    private const MISSING_TARGET = 'Acme\\DoesNotExist\\MissingTarget';
    private const ORPHANED_PLUGIN = 'Acme\\Demo\\Plugin\\OrphanedPlugin';
    private const ACTIVE_PLUGIN = 'Acme\\Demo\\Plugin\\ActivePlugin';
    private const UNKNOWN_CLASS = 'Acme\\Demo\\Service\\NotAPlugin';

    /**
     * @var OrphanedPluginList
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new OrphanedPluginList();
    }

    public function testIsOrphanedPluginReturnsFalseWhenNothingWasCollected(): void
    {
        $this->assertFalse($this->model->isOrphanedPlugin(self::ORPHANED_PLUGIN));
    }

    public function testIsOrphanedPluginReturnsFalseForUnknownClass(): void
    {
        $this->collectDefaultMap();

        $this->assertFalse($this->model->isOrphanedPlugin(self::UNKNOWN_CLASS));
    }

    public function testIsOrphanedPluginReturnsTrueWhenAllTargetsAreMissing(): void
    {
        $this->collectDefaultMap();

        $this->assertTrue($this->model->isOrphanedPlugin(self::ORPHANED_PLUGIN));
    }

    public function testIsOrphanedPluginReturnsFalseWhenAtLeastOneTargetExists(): void
    {
        $this->collectDefaultMap();

        $this->assertFalse($this->model->isOrphanedPlugin(self::ACTIVE_PLUGIN));
    }

    public function testIsOrphanedPluginStripsLeadingBackslash(): void
    {
        $this->collectDefaultMap();

        $this->assertTrue($this->model->isOrphanedPlugin('\\' . self::ORPHANED_PLUGIN));
        $this->assertFalse($this->model->isOrphanedPlugin('\\' . self::ACTIVE_PLUGIN));
    }

    public function testCollectFromPluginDataMergesTargetsAcrossCalls(): void
    {
        $this->model->collectFromPluginData([
            self::MISSING_TARGET => [
                'plugin' => ['instance' => self::ORPHANED_PLUGIN],
            ],
        ]);
        $this->assertTrue($this->model->isOrphanedPlugin(self::ORPHANED_PLUGIN));

        $this->model->collectFromPluginData([
            self::EXISTING_TARGET => [
                'plugin' => ['instance' => '\\' . self::ORPHANED_PLUGIN],
            ],
        ]);
        $this->assertFalse($this->model->isOrphanedPlugin(self::ORPHANED_PLUGIN));
    }

    public function testCollectFromPluginDataIgnoresEntriesWithoutInstance(): void
    {
        $this->model->collectFromPluginData([
            'preferences' => [self::EXISTING_TARGET => self::EXISTING_TARGET],
            self::MISSING_TARGET => [
                'disabled' => ['disabled' => true],
                'orphaned' => ['instance' => self::ORPHANED_PLUGIN],
            ],
        ]);

        $this->assertTrue($this->model->isOrphanedPlugin(self::ORPHANED_PLUGIN));
        $this->assertFalse($this->model->isOrphanedPlugin(self::UNKNOWN_CLASS));
    }

    private function collectDefaultMap(): void
    {
        $this->model->collectFromPluginData([
            self::MISSING_TARGET => [
                'orphaned' => ['instance' => self::ORPHANED_PLUGIN],
            ],
            self::EXISTING_TARGET => [
                'active' => ['instance' => self::ACTIVE_PLUGIN],
            ],
        ]);
    }
}
