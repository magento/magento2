<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Indexer\ConfigInterface as IndexerConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Mview\ConfigInterface as MviewConfigInterface;
use Magento\Framework\Mview\View\Collection;
use Magento\Framework\Mview\View\State\CollectionFactory;
use Magento\Framework\Mview\View\State\CollectionInterface as StateCollectionInterface;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\Mview\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var IndexerConfigInterface|MockObject
     */
    private $indexerConfigMock;

    /**
     * @var EntityFactoryInterface|MockObject
     */
    private $entityFactoryMock;

    /**
     * @var MviewConfigInterface|MockObject
     */
    private $mviewConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $statesFactoryMock;

    /**
     * @var Collection
     */
    private $collection;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->indexerConfigMock = $this->getMockBuilder(IndexerConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->entityFactoryMock = $this->getMockBuilder(EntityFactoryInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->mviewConfigMock = $this->getMockBuilder(MviewConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->statesFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection = $this->objectManagerHelper->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'config' => $this->mviewConfigMock,
                'statesFactory' => $this->statesFactoryMock,
                'indexerConfig' => $this->indexerConfigMock,
            ]
        );
    }

    /**
     * @param array $indexers
     * @param array $views
     * @param array $stateMode
     * @param int $numDisabledViews
     * @param int $numEnabledViews
     * @dataProvider loadDataAndGetViewsByStateModeDataProvider
     */
    public function testLoadDataAndGetViewsByStateMode(
        array $indexers,
        array $views,
        array $stateMode,
        $numDisabledViews,
        $numEnabledViews
    ) {
        $this->indexerConfigMock
            ->method('getIndexers')
            ->willReturn($indexers);

        $this->mviewConfigMock
            ->expects($this->once())
            ->method('getViews')
            ->willReturn(array_flip($views));

        $orderedViews = [];
        foreach ($indexers as $indexerData) {
            $state =  $this->getStateMock(['getMode'], $indexerData);
            $state->method('getMode')
                ->willReturn($stateMode[$indexerData['indexer_id']]);
            $view = $this->getViewMock(['setState', 'getState']);
            $view->expects($this->once())
                ->method('setState');
            $view->method('getState')
                ->willReturn($state);
            $orderedViews[$indexerData['view_id']] = $view;
        }

        $emptyView = $this->getViewMock();
        $emptyView->method('load')
            ->withConsecutive(
                ...array_map(
                    function ($elem) {
                        return [$elem];
                    },
                    array_keys($orderedViews)
                )
            )
            ->willReturnOnConsecutiveCalls(...array_values($orderedViews));

        $indexer = $this->getIndexerMock();
        $indexer->method('load')
            ->willReturnMap(array_map(
                function ($elem) {
                    return [$elem['indexer_id'], $this->getIndexerMock([], $elem)];
                },
                $indexers
            ));

        $this->mviewConfigMock
            ->method('getView')
            ->willReturnMap(array_map(
                function ($elem) {
                    return [$elem, ['view_id' => $elem]];
                },
                $views
            ));

        $this->entityFactoryMock
            ->method('create')
            ->willReturnMap([
                [IndexerInterface::class, [], $indexer],
                [ViewInterface::class, [], $emptyView]
            ]);

        $states = $this->getMockBuilder(StateCollectionInterface::class)
            ->getMockForAbstractClass();
        $states->method('getItems')
            ->willReturn(array_map(
                function ($elem) {
                    return $this->getStateMock([], ['view_id' => $elem]);
                },
                $views
            ));

        $this->statesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($states);

        $this->assertInstanceOf(Collection::class, $this->collection->loadData());

        $views = $this->collection->getViewsByStateMode(StateInterface::MODE_DISABLED);
        $this->assertCount($numDisabledViews, $views);
        $this->assertContainsOnlyInstancesOf(ViewInterface::class, $views);

        $views = $this->collection->getViewsByStateMode(StateInterface::MODE_ENABLED);
        $this->assertCount($numEnabledViews, $views);
    }

    /**
     * @param array $methods
     * @param array $data
     * @return StateInterface|MockObject
     */
    private function getStateMock(array $methods = [], array $data = [])
    {
        $state = $this->getMockBuilder(StateInterface::class)
            ->setMethods(array_merge($methods, ['getViewId']))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $state->method('getViewId')
            ->willReturn($data['view_id'] ?? '');
        return $state;
    }

    /**
     * @param array $methods
     * @return ViewInterface|MockObject
     */
    private function getViewMock(array $methods = [])
    {
        $view = $this->getMockBuilder(ViewInterface::class)
            ->setMethods(array_merge($methods, ['load']))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        return $view;
    }

    /**
     * @param array $methods
     * @param array $data
     * @return MockObject|IndexerInterface
     */
    private function getIndexerMock(array $methods = [], array $data = [])
    {
        /** @var MockObject|IndexerInterface $indexer */
        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->setMethods(array_merge($methods, ['getId', 'getViewId']))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $indexer->method('getId')
            ->willReturn($data['indexer_id'] ?? '');
        $indexer->method('getViewId')
            ->willReturn($data['view_id'] ?? []);
        return $indexer;
    }

    /**
     * @return array
     */
    public function loadDataAndGetViewsByStateModeDataProvider()
    {
        return [
            'Indexers with sequence' => [
                'indexers' => [
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'view_id' => 'view_4',
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'view_id' => 'view_2',
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'view_id' => 'view_1',
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'view_id' => 'view_3',
                    ],
                ],
                'views' => [
                    'view_1',
                    'view_2',
                    'view_3',
                    'view_4',
                ],
                'state_mode' => [
                    'indexer_1' => StateInterface::MODE_DISABLED,
                    'indexer_2' => StateInterface::MODE_DISABLED,
                    'indexer_3' => StateInterface::MODE_DISABLED,
                    'indexer_4' => StateInterface::MODE_ENABLED,
                ],
                'num_disabled_views' => 3,
                'num_enabled_views' => 1,
            ],
        ];
    }
}
