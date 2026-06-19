<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Generator;

use Magento\Framework\App\Area;
use Magento\Setup\Module\Di\Code\Generator\PluginList;
use PHPUnit\Framework\TestCase;

class PluginListTest extends TestCase
{
    /**
     * @var PluginList
     */
    private $pluginList;

    protected function setUp(): void
    {
        $this->pluginList = new class extends \Magento\Setup\Module\Di\Code\Generator\PluginList {
            public function __construct()
            {
            }

            public function populateState(
                array $data,
                array $inherited,
                ?array $processed,
                array $pluginInstances,
                array $loadedScopes,
                array $scopePriorityScheme
            ): void {
                $this->_data = $data;
                $this->_inherited = $inherited;
                $this->_processed = $processed;
                $this->_pluginInstances = $pluginInstances;
                $this->_loadedScopes = $loadedScopes;
                $this->_scopePriorityScheme = $scopePriorityScheme;
            }

            public function exportState(): array
            {
                return [
                    'data' => $this->_data,
                    'inherited' => $this->_inherited,
                    'processed' => $this->_processed,
                    'pluginInstances' => $this->_pluginInstances,
                    'loadedScopes' => $this->_loadedScopes,
                    'scopePriorityScheme' => $this->_scopePriorityScheme,
                ];
            }
        };
    }

    public function testResetClearsLoadedStateAndRestoresGlobalScope(): void
    {
        $this->pluginList->populateState(
            ['type' => ['plugin' => []]],
            ['type' => ['plugin' => ['instance' => 'TestPlugin']]],
            ['type_method___self' => ['plugin' => []]],
            ['type' => ['plugin' => new \stdClass()]],
            ['global' => true, 'frontend' => true],
            ['global', 'frontend']
        );

        $this->pluginList->reset();

        $this->assertSame(
            [
                'data' => [],
                'inherited' => [],
                'processed' => null,
                'pluginInstances' => [],
                'loadedScopes' => [],
                'scopePriorityScheme' => [Area::AREA_GLOBAL],
            ],
            $this->pluginList->exportState()
        );
    }
}
