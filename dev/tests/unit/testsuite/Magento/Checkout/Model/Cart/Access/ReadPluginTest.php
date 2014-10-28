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

namespace Magento\Checkout\Model\Cart\Access;

class ReadPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Model\Cart\Access\ReadPlugin
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->userContextMock = $this->getMock('Magento\Authorization\Model\UserContextInterface');
        $this->subjectMock = $this->getMock('\Magento\Checkout\Service\V1\Cart\ReadServiceInterface');
        $this->model = new ReadPlugin($this->userContextMock);
    }

    /**
     * @param int $userType
     * @dataProvider successTypeDataProvider
     */
    public function testBeforeGetCartSuccess($userType)
    {
        $this->userContextMock->expects($this->once())->method('getUserType')->will($this->returnValue($userType));
        $this->model->beforeGetCart($this->subjectMock, 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage Access denied
     */
    public function testBeforeGetCartDenied()
    {
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->will($this->returnValue(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER));
        $this->model->beforeGetCart($this->subjectMock, 1);
    }

    public function successTypeDataProvider()
    {
        return [
            'admin' => [\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN],
            'integration' => [\Magento\Authorization\Model\UserContextInterface::USER_TYPE_INTEGRATION],
        ];
    }

    /**
     * @param int $userType
     * @dataProvider successTypeDataProvider
     */
    public function testBeforeGetCartListSuccess($userType)
    {
        $this->userContextMock->expects($this->once())->method('getUserType')->will($this->returnValue($userType));
        $this->model->beforeGetCartList(
            $this->subjectMock,
            $this->getMock('\Magento\Framework\Service\V1\Data\SearchCriteria', [], [], '', false)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage Access denied
     */
    public function testBeforeGetCartListDenied()
    {
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->will($this->returnValue(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER));
        $this->model->beforeGetCartList(
            $this->subjectMock,
            $this->getMock('\Magento\Framework\Service\V1\Data\SearchCriteria', [], [], '', false)
        );
    }
}
