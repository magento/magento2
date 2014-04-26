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

namespace Magento\Review\Block\Adminhtml;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccount;

    public function testConstruct()
    {
        $this->customerAccount = $this
            ->getMockForAbstractClass('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $this->customerAccount->expects($this->once())
            ->method('getCustomer')
            ->with('customer id')
            ->will($this->returnValue(new \Magento\Framework\Object()));
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
                'customerAccount' => $this->customerAccount
            ]
        );
    }
}
