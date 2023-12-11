<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\Session\SessionManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Framework\Data\Form\FormKey
 */
class FormKeyTest extends TestCase
{
    /**
     * @var Random|MockObject
     */
    protected $mathRandomMock;

    /**
     * @var SessionManager|MockObject
     */
    protected $sessionMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var FormKey
     */
    protected $formKey;

    protected function setUp(): void
    {
        $this->mathRandomMock = $this->createMock(Random::class);
        $this->sessionMock = $this->getMockBuilder(SessionManager::class)
            ->addMethods(['setData'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeJs')->willReturnArgument(0);
        $this->formKey = new FormKey(
            $this->mathRandomMock,
            $this->sessionMock,
            $this->escaperMock
        );
    }

    public function testGetFormKeyNotExist()
    {
        $valueMap = [
            [FormKey::FORM_KEY, false, null],
            [FormKey::FORM_KEY, false, 'random_string'],
        ];
        $this->sessionMock
            ->expects($this->any())
            ->method('getData')
            ->willReturnMap($valueMap);
        $this->mathRandomMock
            ->expects($this->once())
            ->method('getRandomString')
            ->with(16)
            ->willReturn('random_string');
        $this->sessionMock->expects($this->once())->method('setData')->with(FormKey::FORM_KEY, 'random_string');
        $this->formKey->getFormKey();
    }

    public function testGetFormKeyExists()
    {
        $this->sessionMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->with(FormKey::FORM_KEY)
            ->willReturn('random_string');
        $this->mathRandomMock
            ->expects($this->never())
            ->method('getRandomString');
        $this->sessionMock->expects($this->never())->method('setData');
        $this->assertEquals('random_string', $this->formKey->getFormKey());
    }

    public function testIsPresent()
    {
        $this->sessionMock->expects(static::once())
            ->method('getData')
            ->with(FormKey::FORM_KEY)
            ->willReturn('Form key');

        static::assertTrue($this->formKey->isPresent());
    }

    public function testSet()
    {
        $formKeyValue = 'Form key';

        $this->sessionMock->expects(static::once())
            ->method('setData')
            ->with(FormKey::FORM_KEY, $formKeyValue);

        $this->formKey->set($formKeyValue);
    }
}
