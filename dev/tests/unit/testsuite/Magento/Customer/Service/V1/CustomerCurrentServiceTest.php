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

namespace Magento\Customer\Service\V1;


class CustomerCurrentServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Service\V1\CustomerCurrentService
     */
    protected $customerCurrentService;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Customer\Service\V1\Dto\CustomerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDtoBuilderMock;

    /**
     * @var \Magento\Customer\Service\V1\Dto\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDtoMock;

    /**
     * @var \Magento\Customer\Service\V1\CustomerService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerServiceMock;

    /**
     * @var \Magento\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\App\ViewInterface
     */
    protected $viewMock;
    /**
     * @var int
     */
    protected $customerId = 100;

    /**
     * @var int
     */
    protected $customerGroupId = 500;

    /**
     * Test setup
     */
    public function setUp()
    {
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session',
            array(), array(), '', false);
        $this->layoutMock = $this->getMock('Magento\Core\Model\Layout',
            array(), array(), '', false);
        $this->customerDtoBuilderMock = $this->getMock('Magento\Customer\Service\V1\Dto\CustomerBuilder',
            array('create', 'setGroupId'), array(), '', false);
        $this->customerDtoMock = $this->getMock('Magento\Customer\Service\V1\Dto\Customer',
            array(), array(), '', false);
        $this->customerServiceMock = $this->getMock('Magento\Customer\Service\V1\CustomerService',
            array(), array(), '', false);
        $this->requestMock = $this->getMock('Magento\App\Request\Http',
            array(), array(), '', false);
        $this->moduleManagerMock = $this->getMock('Magento\Module\Manager',
            array(), array(), '', false);
        $this->viewMock = $this->getMock('Magento\App\View',
            array(), array(), '', false);

        $this->customerCurrentService = new \Magento\Customer\Service\V1\CustomerCurrentService(
            $this->customerSessionMock,
            $this->layoutMock,
            $this->customerDtoBuilderMock,
            $this->customerServiceMock,
            $this->requestMock,
            $this->moduleManagerMock,
            $this->viewMock
        );
    }

    /**
     * test getCustomer method, method returns depersonalized customer Dto
     */
    public function testGetCustomerDepersonalizeCustomerDto()
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));
        $this->viewMock->expects($this->once())
            ->method('isLayoutLoaded')
            ->will($this->returnValue(true));
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue($this->customerGroupId));
        $this->customerDtoBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerDtoMock));
        $this->customerDtoBuilderMock->expects($this->once())
            ->method('setGroupId')
            ->with($this->equalTo($this->customerGroupId))
            ->will($this->returnSelf());
        $this->assertEquals($this->customerDtoMock, $this->customerCurrentService->getCustomer());
    }

    /**
     * test get customer method, method returns customer from service
     */
    public function testGetCustomerLoadCustomerFromService()
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(false));
        $this->customerSessionMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->customerId));
        $this->customerServiceMock->expects($this->once())
            ->method('getCustomer')
            ->with($this->equalTo($this->customerId))
            ->will($this->returnValue($this->customerDtoMock));
        $this->assertEquals($this->customerDtoMock, $this->customerCurrentService->getCustomer());
    }
}
