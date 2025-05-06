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

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Comment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Invoice\Comment;
use Magento\Sales\Model\Order\Invoice\Comment\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Helper\SalesEntityCommentValidator;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Comment|MockObject
     */
    protected $commentModelMock;

    /**
     * @var SalesEntityCommentValidator|MockObject
     */
    private $salesEntityCommentValidator;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->salesEntityCommentValidator = $this->getMockBuilder(SalesEntityCommentValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->commentModelMock = $this->createPartialMock(
            Comment::class,
            ['hasData', 'getData']
        );
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'salesEntityCommentValidator' => $this->salesEntityCommentValidator
            ]
        );
    }

    /**
     * Run test validate
     *
     * @param $commentDataMap
     * @param $commentData
     * @param $expectedWarnings
     * @dataProvider providerCommentData
     */
    public function testValidate($commentDataMap, $commentData, $expectedWarnings)
    {
        $this->commentModelMock->expects($this->any())
            ->method('hasData')
            ->willReturnMap($commentDataMap);
        $this->commentModelMock->expects($this->once())
            ->method('getData')
            ->willReturn($commentData);
        $actualWarnings = $this->validator->validate($this->commentModelMock);
        $this->assertEquals($expectedWarnings, $actualWarnings);
    }

    /**
     * Provides comment data for tests
     *
     * @return array
     */
    public static function providerCommentData()
    {
        return [
            [
                [
                    ['parent_id', true],
                    ['comment', true],
                ],
                [
                    'parent_id' => 25,
                    'comment' => 'Hello World!'
                ],
                [
                    'comment' => 'User is not authorized to edit comment.'
                ]
            ],
            [
                [
                    ['parent_id', true],
                    ['comment', false],
                ],
                [
                    'parent_id' => 0,
                    'comment' => null
                ],
                [
                    'parent_id' => 'Parent Invoice Id can not be empty',
                    'comment' => '"Comment" is required. Enter and try again.'
                ]
            ]
        ];
    }
}
