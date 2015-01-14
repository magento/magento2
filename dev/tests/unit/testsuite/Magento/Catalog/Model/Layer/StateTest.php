<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

use Magento\TestFramework\Helper\ObjectManager;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\State
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $item;

    protected function setUp()
    {
        $this->item = $this->getMockBuilder('Magento\Catalog\Model\Layer\Filter\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject('Magento\Catalog\Model\Layer\State');
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testSetFiltersException()
    {
        $this->model->setFilters($this->item);
    }

    public function testSetFilters()
    {
        $expect = [$this->item];

        $this->model->setFilters($expect);
        $this->assertEquals($expect, $this->model->getFilters());
    }

    public function testAddFilter()
    {
        $expect = [$this->item];

        $this->model->addFilter($this->item);

        $this->assertEquals($expect, $this->model->getFilters());
    }
}
