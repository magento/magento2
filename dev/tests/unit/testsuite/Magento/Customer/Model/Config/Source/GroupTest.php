<?php
/**
 *
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
namespace Magento\Customer\Model\Config\Source;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Config\Source\Group
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    protected function setUp()
    {
        $this->groupServiceMock = $this->getMock('\Magento\Customer\Service\V1\CustomerGroupServiceInterface');
        $this->converterMock = $this->getMock('\Magento\Framework\Convert\Object', array(), array(), '', false);
        $this->model =
            new \Magento\Customer\Model\Config\Source\Group($this->groupServiceMock, $this->converterMock);
    }

    public function testToOptionArray()
    {
        $expectedValue = array('General', 'Retail');
        $this->groupServiceMock->expects($this->once())->method('getGroups')
            ->with(false)->will($this->returnValue($expectedValue));
        $this->converterMock->expects($this->once())->method('toOptionArray')
            ->with($expectedValue, 'id', 'code')->will($this->returnValue($expectedValue));
        array_unshift($expectedValue, array('value'=> '', 'label'=> __('-- Please Select --')));
        $this->assertEquals($expectedValue, $this->model->toOptionArray());
    }
}
