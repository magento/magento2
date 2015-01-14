<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Model\Observer
     */
    private $_model = null;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea('crontab')
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Cron\Model\Observer');
        $this->_model->dispatch('this argument is not used');
    }

    /**
     * @magentoConfigFixture current_store crontab/default/jobs/catalog_product_alert/schedule/cron_expr 8 * * * *
     */
    public function testDispatchScheduled()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cron\Model\Resource\Schedule\Collection'
        );
        $collection->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_PENDING);
        $this->assertGreaterThan(0, $collection->count(), 'Cron has failed to schedule tasks for itself for future.');
    }

    public function testDispatchNoFailed()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cron\Model\Resource\Schedule\Collection'
        );
        $collection->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_ERROR);
        foreach ($collection as $item) {
            $this->fail($item->getMessages());
        }
    }
}
