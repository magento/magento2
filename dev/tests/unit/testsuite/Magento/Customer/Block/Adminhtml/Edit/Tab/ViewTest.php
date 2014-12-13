<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
        $registry = $this->getMock('Magento\Framework\Registry');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $objectManagerHelper->getObject(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\View',
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
