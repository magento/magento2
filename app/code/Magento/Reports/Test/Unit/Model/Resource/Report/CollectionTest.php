<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Model\Resource\Report;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Resource\Report\Collection
     */
    protected $_model;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $helper->getObject('Magento\Reports\Model\Resource\Report\Collection');
    }

    public function testGetIntervalsWithoutSpecifiedPeriod()
    {
        $startDate = new \DateTime('-3 day');
        $endDate = new \DateTime('+3 day');
        $this->_model->setInterval($startDate, $endDate);

        $this->assertEquals(0, $this->_model->getSize());
    }

    public function testGetIntervalsWithoutSpecifiedInterval()
    {
        $this->assertEquals(0, $this->_model->getSize());
    }
}
