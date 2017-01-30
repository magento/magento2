<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Observer;

use \Magento\TestFramework\Helper\Bootstrap;

class ProcessCronQueueObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Observer\ProcessCronQueueObserver
     */
    private $_model = null;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea('crontab')
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
        $request = Bootstrap::getObjectManager()->create('Magento\Framework\App\Console\Request');
        $request->setParams(['group' => 'default', 'standaloneProcessStarted' => '0']);
        $this->_model = Bootstrap::getObjectManager()
            ->create('Magento\Cron\Observer\ProcessCronQueueObserver', ['request' => $request]);
        $this->_model->execute(new \Magento\Framework\Event\Observer());
    }

    /**
     * @magentoConfigFixture current_store crontab/default/jobs/catalog_product_alert/schedule/cron_expr 8 * * * *
     */
    public function testDispatchScheduled()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cron\Model\ResourceModel\Schedule\Collection'
        );
        $collection->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_PENDING);
        $this->assertGreaterThan(0, $collection->count(), 'Cron has failed to schedule tasks for itself for future.');
    }

    public function testDispatchNoFailed()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cron\Model\ResourceModel\Schedule\Collection'
        );
        $collection->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_ERROR);
        foreach ($collection as $item) {
            $this->fail($item->getMessages());
        }
    }
}
