<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Observer;

use Magento\Cron\Observer\ProcessCronQueueObserver;
use \Magento\TestFramework\Helper\Bootstrap;

class ProcessCronQueueObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cron\Observer\ProcessCronQueueObserver
     */
    private $_model = null;

    protected function setUp(): void
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\AreaList::class)
            ->getArea('crontab')
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
        $request = Bootstrap::getObjectManager()->create(\Magento\Framework\App\Console\Request::class);
        $request->setParams(['group' => 'default', 'standaloneProcessStarted' => '0']);
        $this->_model = Bootstrap::getObjectManager()
            ->create(\Magento\Cron\Observer\ProcessCronQueueObserver::class, ['request' => $request]);
        $this->_model->execute(new \Magento\Framework\Event\Observer());
    }

    /**
     * @magentoConfigFixture current_store crontab/default/jobs/catalog_product_alert/schedule/cron_expr * * * * *
     */
    public function testDispatchScheduled()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cron\Model\ResourceModel\Schedule\Collection::class
        );
        $collection->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_PENDING);
        $collection->addFieldToFilter('job_code', 'catalog_product_alert');
        $this->assertGreaterThan(0, $collection->count(), 'Cron has failed to schedule tasks for itself for future.');
    }

    public function testDispatchNoFailed()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cron\Model\ResourceModel\Schedule\Collection::class
        );
        $collection->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_ERROR);
        foreach ($collection as $item) {
            $this->fail($item->getMessages());
        }
    }

    /**
     * @param array $expectedGroupsToRun
     * @param null $group
     * @param null $excludeGroup
     * @dataProvider groupFiltersDataProvider
     */
    public function testGroupFilters(array $expectedGroupsToRun, $group = null, $excludeGroup = null)
    {
        $config = $this->createMock(\Magento\Cron\Model\ConfigInterface::class);
        $config->expects($this->any())
            ->method('getJobs')
            ->willReturn($this->getFilterTestCronGroups());

        $request = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Console\Request::class);
        $lockManager = $this->createMock(\Magento\Framework\Lock\LockManagerInterface::class);

        // The jobs are locked when they are run, assert on them to see which groups would run
        $expectedLockData = [];
        foreach ($expectedGroupsToRun as $expectedGroupToRun) {
            $expectedLockData[] = [
                ProcessCronQueueObserver::LOCK_PREFIX . $expectedGroupToRun,
                ProcessCronQueueObserver::LOCK_TIMEOUT
            ];
        }

        // No expected lock data, means we should never call it
        if (empty($expectedLockData)) {
            $lockManager->expects($this->never())
                ->method('lock');
        }

        $lockManager->expects($this->exactly(count($expectedLockData)))
            ->method('lock')
            ->withConsecutive(...$expectedLockData);

        $request->setParams(
            [
                'group' => $group,
                'exclude-group' => $excludeGroup,
                'standaloneProcessStarted' => '1'
            ]
        );
        $this->_model = Bootstrap::getObjectManager()
            ->create(\Magento\Cron\Observer\ProcessCronQueueObserver::class, [
                'request' => $request,
                'lockManager' => $lockManager,
                'config' => $config
            ]);
        $this->_model->execute(new \Magento\Framework\Event\Observer());
    }

    /**
     * @return array|array[]
     */
    public function groupFiltersDataProvider(): array
    {

        return [
            'no flags runs all groups' => [
                ['index', 'consumers', 'default']    // groups to run
            ],
            '--group=default should run'  => [
                ['default'],                        // groups to run
                'default',                          // --group default
            ],
            '--group=default with --exclude-group=default, nothing should run' => [
                [],                                 // groups to run
                'default',                          // --group default
                ['default'],                        // --exclude-group default
            ],
            '--group=default with --exclude-group=index, default should run' => [
                ['default'],                        // groups to run
                'default',                          // --group default
                ['index'],                          // --exclude-group index
            ],
            '--group=index with --exclude-group=default, index should run' => [
                ['index'],                          // groups to run
                'index',                            // --group index
                ['default'],                        // --exclude-group default
            ],
            '--exclude-group=index, all other groups should run' => [
                ['consumers', 'default'],           // groups to run, all but index
                null,                               //
                ['index']                           // --exclude-group index
            ],
            '--exclude-group for every group runs nothing' => [
                [],                                 // groups to run, none
                null,                               //
                ['default', 'consumers', 'index']   // groups to exclude, all of them
            ],
            'exclude all groups but consumers, consumers runs' => [
                ['consumers'],
                null,
                ['index', 'default']
            ],
        ];
    }

    /**
     * Only run the filter group tests with a limited set of cron groups, keeps tests consistent between EE and CE
     *
     * @return array
     */
    private function getFilterTestCronGroups()
    {
        $listOfGroups = [];
        $config = Bootstrap::getObjectManager()->get(\Magento\Cron\Model\ConfigInterface::class);
        foreach ($config->getJobs() as $groupId => $data) {
            if (in_array($groupId, ['default', 'consumers', 'index'])) {
                $listOfGroups[$groupId] = $data;
            }
        }
        return $listOfGroups;
    }
}
