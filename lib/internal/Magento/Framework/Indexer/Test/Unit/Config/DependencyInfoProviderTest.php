<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Test\Unit\Config;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\Config\DependencyInfoProvider;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DependencyInfoProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var DependencyInfoProvider
     */
    private $dependencyInfoProvider;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->dependencyInfoProvider = $this->objectManagerHelper->getObject(
            DependencyInfoProvider::class,
            [
                'config' => $this->configMock,
            ]
        );
    }

    public function testGetDependencies()
    {
        $indexerId = 'indexer_1';
        $dependencies = [
            'indexer_2',
            'indexer_3',
        ];
        $this->addSeparateIndexersToConfigMock([
            [
                'indexer_id' => $indexerId,
                'dependencies' => $dependencies,
            ]
        ]);
        $this->assertSame($dependencies, $this->dependencyInfoProvider->getIndexerIdsToRunBefore($indexerId));
    }

    public function testGetDependenciesNonExistentIndexer()
    {
        $indexerId = 'indexer_1';
        $this->setExpectedException(
            NoSuchEntityException::class,
            "{$indexerId} indexer does not exist."
        );
        $this->dependencyInfoProvider->getIndexerIdsToRunBefore($indexerId);
    }

    /**
     * @param string $indexerId
     * @param array $indexersData
     * @param array $dependencySequence
     * @dataProvider getDependencySequenceDataProvider
     */
    /*public function testGetDependencySequence(string $indexerId, array $indexersData, array $dependencySequence)
    {
        $this->addSeparateIndexersToConfigMock($indexersData);
        $this->addAllIndexersToConfigMock($indexersData);
        $this->assertSame($dependencySequence, $this->dependencyProvider->getDependencySequence($indexerId));
    }*/

    /**
     * @return array
     */
    /*public function getDependencySequenceDataProvider()
    {
        return [
            [
                'indexer' => 'indexer_1',
                'indexers' => [
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'dependencies' => [],
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'dependencies' => [],
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'dependencies' => [
                            'indexer_4',
                        ],
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'dependencies' => [
                            'indexer_2',
                            'indexer_3',
                        ],
                    ],
                    'indexer_5' => [
                        'indexer_id' => 'indexer_5',
                        'dependencies' => [],
                    ],
                ],
                'dependency_sequence' => ['indexer_2', 'indexer_4', 'indexer_3'],
            ]
        ];
    }*/

    /*public function testGetDependencySequenceNonExistentIndexer()
    {
        $indexerId = 'indexer_1';
        $this->setExpectedException(
            NoSuchEntityException::class,
            "{$indexerId} indexer does not exist."
        );
        $this->dependencyProvider->getDependentSequence($indexerId);
    }*/

    /**
     * @param string $indexerId
     * @param array $indexersData
     * @param array $dependentIndexerIds
     * @dataProvider getDependentIndexerIdsDataProvider
     */
    public function testGetDependentIndexerIds(string $indexerId, array $indexersData, array $dependentIndexerIds)
    {
        $this->addSeparateIndexersToConfigMock($indexersData);
        $this->addAllIndexersToConfigMock($indexersData);
        $this->assertSame(
            $dependentIndexerIds,
            array_values($this->dependencyInfoProvider->getIndexerIdsToRunAfter($indexerId))
        );
    }

    /**
     * @return array
     */
    public function getDependentIndexerIdsDataProvider()
    {
        return [
            [
                'indexer' => 'indexer_2',
                'indexers' => [
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'dependencies' => [],
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'dependencies' => [
                            'indexer_2',
                        ],
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'dependencies' => [
                            'indexer_4',
                        ],
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'dependencies' => [
                            'indexer_2',
                            'indexer_3',
                        ],
                    ],
                    'indexer_5' => [
                        'indexer_id' => 'indexer_5',
                        'dependencies' => [],
                    ],
                ],
                'dependent_indexers' => ['indexer_4', 'indexer_1'],
            ]
        ];
    }

    public function testGetDependentIndexerIdsNonExistentIndexer()
    {
        $indexerId = 'indexer_1';
        $this->setExpectedException(
            NoSuchEntityException::class,
            "{$indexerId} indexer does not exist."
        );
        $this->dependencyInfoProvider->getIndexerIdsToRunAfter($indexerId);
    }

    /**
     * @param string $indexerId
     * @param array $indexersData
     * @param array $dependentSequence
     * @dataProvider getDependentSequenceDataProvider
     */
    /*public function testGetDependentSequence(string $indexerId, array $indexersData, array $dependentSequence)
    {
        $this->addSeparateIndexersToConfigMock($indexersData);
        $this->addAllIndexersToConfigMock($indexersData);
        $this->assertSame(
            $dependentSequence,
            array_values($this->dependencyProvider->getDependentSequence($indexerId))
        );
    }*/

    /**
     * @return array
     */
    /*public function getDependentSequenceDataProvider()
    {
        return [
            [
                'indexer' => 'indexer_4',
                'indexers' => [
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'dependencies' => [],
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'dependencies' => [
                            'indexer_2',
                        ],
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'dependencies' => [
                            'indexer_4',
                        ],
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'dependencies' => [
                            'indexer_2',
                            'indexer_3',
                        ],
                    ],
                    'indexer_5' => [
                        'indexer_id' => 'indexer_5',
                        'dependencies' => [
                            'indexer_1',
                        ],
                    ],
                ],
                'dependent_sequence' => ['indexer_3', 'indexer_1', 'indexer_5'],
            ]
        ];
    }*/

    /**
     * @param array $indexers
     */
    private function addSeparateIndexersToConfigMock(array $indexers)
    {
        $this->configMock
            ->method('getIndexer')
            ->willReturnMap(
                array_map(
                    function ($elem) {
                        return [$elem['indexer_id'], $elem];
                    },
                    $indexers
                )
            );
    }

    /**
     * @param array $indexers
     */
    private function addAllIndexersToConfigMock(array $indexers)
    {
        $this->configMock
            ->method('getIndexers')
            ->willReturn($indexers);
    }
}
