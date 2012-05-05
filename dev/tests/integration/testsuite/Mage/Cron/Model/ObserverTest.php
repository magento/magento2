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
 * @category    Magento
 * @package     Mage_Cron
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Cron_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Cron_Model_Observer
     */
    private $_model = null;

    public function setUp()
    {
        $this->_model = new Mage_Cron_Model_Observer;
        $this->_model->dispatch('this argument is not used');
    }

    public function testDispatchScheduled()
    {
        $collection = new Mage_Cron_Model_Resource_Schedule_Collection;
        $collection->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING);
        $this->assertGreaterThan(0, $collection->count(), 'Cron has failed to schedule tasks for itself for future.');
    }

    public function testDispatchNoFailed()
    {
        $collection = new Mage_Cron_Model_Resource_Schedule_Collection;
        $collection->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_ERROR);
        foreach ($collection as $item) {
            $this->fail($item->getMessages());
        }
    }
}
