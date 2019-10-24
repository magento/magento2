<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\PageRepository;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageRepository\ValidationComposite;
use Magento\Cms\Model\PageRepository\ValidatorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Validate behavior of the validation composite
 */
class ValidationCompositeTest extends TestCase
{
    /**
     * @var PageRepositoryInterface|MockObject
     */
    private $subject;

    protected function setUp()
    {
        /** @var PageRepositoryInterface subject */
        $this->subject = $this->createMock(PageRepositoryInterface::class);
    }

    /**
     * @param $validators
     * @expectedException \InvalidArgumentException
     * @dataProvider constructorArgumentProvider
     */
    public function testConstructorValidation($validators)
    {
        new ValidationComposite($this->subject, $validators);
    }

    public function testSaveInvokesValidatorsWithSucess()
    {
        $validator1 = $this->createMock(ValidatorInterface::class);
        $validator2 = $this->createMock(ValidatorInterface::class);
        $page = $this->createMock(PageInterface::class);

        // Assert each are called
        $validator1
            ->expects($this->once())
            ->method('validate')
            ->with($page);
        $validator2
            ->expects($this->once())
            ->method('validate')
            ->with($page);

        // Assert that the success is called
        $this->subject
            ->expects($this->once())
            ->method('save')
            ->with($page)
            ->willReturn('foo');

        $composite = new ValidationComposite($this->subject, [$validator1, $validator2]);
        $result = $composite->save($page);

        self::assertSame('foo', $result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Oh no. That isn't right.
     */
    public function testSaveInvokesValidatorsWithErrors()
    {
        $validator1 = $this->createMock(ValidatorInterface::class);
        $validator2 = $this->createMock(ValidatorInterface::class);
        $page = $this->createMock(PageInterface::class);

        // Assert the first is called
        $validator1
            ->expects($this->once())
            ->method('validate')
            ->with($page)
            ->willThrowException(new LocalizedException(__('Oh no. That isn\'t right.')));

        // Assert the second is NOT called
        $validator2
            ->expects($this->never())
            ->method('validate');

        // Assert that the success is NOT called
        $this->subject
            ->expects($this->never())
            ->method('save');

        $composite = new ValidationComposite($this->subject, [$validator1, $validator2]);
        $composite->save($page);
    }

    /**
     * @param $method
     * @param $arg
     * @dataProvider passthroughMethodDataProvider
     */
    public function testPassthroughMethods($method, $arg)
    {
        $this->subject
            ->method($method)
            ->with($arg)
            ->willReturn('foo');

        $composite = new ValidationComposite($this->subject, []);
        $result = $composite->{$method}($arg);

        self::assertSame('foo', $result);
    }

    public function constructorArgumentProvider()
    {
        return [
            [[null], false],
            [[''], false],
            [['foo'], false],
            [[new \stdClass()], false],
            [[$this->createMock(ValidatorInterface::class), 'foo'], false],
        ];
    }

    public function passthroughMethodDataProvider()
    {
        return [
            ['save', $this->createMock(PageInterface::class)],
            ['getById', 1],
            ['getList', $this->createMock(SearchCriteriaInterface::class)],
            ['delete', $this->createMock(PageInterface::class)],
            ['deleteById', 1],
        ];
    }
}
