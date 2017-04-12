<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ViewTest
 * @package Magento\Customer\Block\Adminhtml\Edit\Tab
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Edit\Tab\View
     */
    protected $view;

    protected function setUp()
    {
        $registry = $this->getMock(\Magento\Framework\Registry::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $objectManagerHelper->getObject(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\View::class,
            [
                'registry' => $registry
            ]
        );
    }

    public function testGetTabLabel()
    {
        $this->assertEquals('Customer View', $this->view->getTabLabel());
    }

    public function testGetTabTitle()
    {
        $this->assertEquals('Customer View', $this->view->getTabTitle());
    }
}
