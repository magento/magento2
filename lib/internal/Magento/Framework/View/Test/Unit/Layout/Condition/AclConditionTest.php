<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Condition;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Layout\AclCondition;

class AclConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AclCondition
     */
    protected $model;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    protected function setUp()
    {
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMock();
        $this->model = new AclCondition($this->authorizationMock);
    }

    public function testFilterAclElements()
    {
        $this->authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturnMap(
                [
                    ['acl_authorised', null, true],
                    ['acl_non_authorised', null, false],
                ]
            );
        $this->assertTrue($this->model->isVisible(['acl' => 'acl_authorised']));
        $this->assertFalse($this->model->isVisible(['acl' => 'acl_non_authorised']));
    }
}
