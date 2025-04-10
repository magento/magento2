<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Helper\SalesEntityCommentValidator;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Sales\Model\Order\Invoice\Comment as InvoiceComment;
use PHPUnit\Framework\TestCase;

/**
 * Test cases of sales entity comment validator test
 */
class SalesEntityCommentValidatorTest extends TestCase
{
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContextMock;

    /**
     * @var InvoiceComment
     */
    private InvoiceComment $invoiceComment;

    /**
     * @var SalesEntityCommentValidator
     */
    private $helper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceComment = $this->getMockBuilder(InvoiceComment::class)
            ->onlyMethods(['getData','getId','setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->helper = $objectManager->getObject(
            SalesEntityCommentValidator::class,
            [
                'userContext' => $this->userContextMock
            ]
        );
    }

    /**
     * Tests that comment is allowed to edit
     *
     * @dataProvider commentDataProvider
     * @param $userId
     * @param $userType
     * @param $commentData
     * @param $result
     * @return void
     */
    public function testIsEditCommentAllowed($userId, $userType, $commentData, $result): void
    {
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn($userId);
        $this->userContextMock->expects($this->any())->method('getUserType')->willReturn($userType);
        $this->invoiceComment->expects($this->any())->method('getId')->willReturn($userId);
        $this->invoiceComment->expects($this->any())->method('getData')->willReturnMap([
            ['user_id', $commentData],
            ['user_type', $commentData],
        ]);

        $this->assertEquals($result, $this->helper->isEditCommentAllowed($this->invoiceComment));
    }

    /**
     * Data provider for comment validation
     *
     * @return array[]
     */
    public static function commentDataProvider(): array
    {
        return [
            [
                1,2,5,false
            ],
            [
                0,1,1,true
            ]
        ];
    }
}
