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

            public function populateState(array $state): void
            {
                $this->_data = $state['data'];
                $this->_inherited = $state['inherited'];
                $this->_processed = $state['processed'];
                $this->_pluginInstances = $state['pluginInstances'];
                $this->_loadedScopes = $state['loadedScopes'];
                $this->_scopePriorityScheme = $state['scopePriorityScheme'];
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
            [
                'data' => ['type' => ['plugin' => []]],
                'inherited' => ['type' => ['plugin' => ['instance' => 'TestPlugin']]],
                'processed' => ['type_method___self' => ['plugin' => []]],
                'pluginInstances' => ['type' => ['plugin' => new \stdClass()]],
                'loadedScopes' => ['global' => true, 'frontend' => true],
                'scopePriorityScheme' => ['global', 'frontend'],
            ]
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
