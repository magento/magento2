<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Import\Frame;

use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\EncoderInterface;

/**
 * Unit test for Magento\ImportExport\Block\Adminhtml\Import\Frame\Result
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResultTest extends TestCase
{
    /**
     * @var Result
     */
    private $result;

    /**
     * @var EncoderInterface|MockObject
     */
    private $encoderMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * Initialize Class Dependencies
     *
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->encoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->escaperMock = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
        $this->result = new Result(
            $this->contextMock,
            $this->encoderMock
        );
    }

    /**
     * Test error message
     *
     * @return void
     */
    public function testAddError(): void
    {
        $errors = ['first error', 'second error','third error'];
        $this->escaperMock
            ->expects($this->exactly(count($errors)))
            ->method('escapeHtml')
            ->willReturnOnConsecutiveCalls(...array_values($errors));

        $this->result->addError($errors);
        $this->assertEquals(count($errors), count($this->result->getMessages()['error']));
    }

    /**
     * Test success message
     *
     * @return void
     */
    public function testAddSuccess(): void
    {
        $success = ['first message', 'second message','third message'];
        $this->escaperMock
            ->expects($this->exactly(count($success)))
            ->method('escapeHtml')
            ->willReturnOnConsecutiveCalls(...array_values($success));

        $this->result->addSuccess($success);
        $this->assertEquals(count($success), count($this->result->getMessages()['success']));
    }

    /**
     * Test Add Notice message
     *
     * @return void
     */
    public function testAddNotice(): void
    {
        $notice = ['notice 1', 'notice 2','notice 3'];

        $this->result->addNotice($notice);
        $this->assertEquals(count($notice), count($this->result->getMessages()['notice']));
    }
}
