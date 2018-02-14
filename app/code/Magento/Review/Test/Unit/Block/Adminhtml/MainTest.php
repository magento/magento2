<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Block\Adminhtml;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Review\Block\Adminhtml\Main
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerViewHelper;

    public function testConstruct()
    {
        $this->customerRepository = $this
            ->getMockForAbstractClass('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->customerViewHelper = $this->getMock('Magento\Customer\Helper\View', [], [], '', false);
        $dummyCustomer = $this->getMockForAbstractClass('Magento\Customer\Api\Data\CustomerInterface');

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with('customer id')
            ->will($this->returnValue($dummyCustomer));
        $this->customerViewHelper->expects($this->once())
            ->method('getCustomerName')
            ->with($dummyCustomer)
            ->will($this->returnValue(new \Magento\Framework\DataObject()));
        $this->request = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface');
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('customerId', false)
            ->will($this->returnValue('customer id'));
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('productId', false)
            ->will($this->returnValue(false));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Review\Block\Adminhtml\Main',
            [
                'request' => $this->request,
                'customerRepository' => $this->customerRepository,
                'customerViewHelper' => $this->customerViewHelper
            ]
        );
    }
}
