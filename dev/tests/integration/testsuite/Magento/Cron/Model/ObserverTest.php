<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
