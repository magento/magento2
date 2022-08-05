<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator\LayoutUpdate;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\View\Model\Layout\Update\Validator;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test validation for layout update
 */
class LayoutUpdateTest extends TestCase
{
    /**
     * @var LayoutUpdate|MockObject
     */
    private $validator;

    /**
     * @var Validator|MockObject
     */
    private $layoutValidator;

    protected function setUp(): void
    {
        $validatorFactory = $this->createMock(ValidatorFactory::class);
        $validationState = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $this->layoutValidator = $this->createMock(Validator::class);
        $validatorFactory->method('create')
            ->with(['validationState' => $validationState])
            ->willReturn($this->layoutValidator);

        $this->validator = new LayoutUpdate(
            $validatorFactory,
            $validationState
        );
    }

    public function testValidationIsSkippedWithDataNotPresent()
    {
        $this->layoutValidator
            ->expects($this->never())
            ->method('isValid');

        $result = $this->validator->isValid([]);
        self::assertTrue($result);
    }

    public function testValidationFailsProperly()
    {
        $this->layoutValidator
            ->method('isValid')
            ->with('foo')
            ->willReturn(false);

        $contextMock = $this->createMock(Product::class);
        $contextMock
            ->method('retrieveMessageTemplate')
            ->with('invalidLayoutUpdate')
            ->willReturn('oh no');
        $this->validator->init($contextMock);

        $result = $this->validator->isValid(['custom_layout_update' => 'foo']);
        $messages = $this->validator->getMessages();
        self::assertFalse($result);
        self::assertSame(['oh no'], $messages);
    }

    public function testInvalidDataException()
    {
        $this->layoutValidator
            ->method('isValid')
            ->willThrowException(new \Exception('foo'));

        $contextMock = $this->createMock(Product::class);
        $contextMock
            ->method('retrieveMessageTemplate')
            ->with('invalidLayoutUpdate')
            ->willReturn('oh no');
        $this->validator->init($contextMock);

        $result = $this->validator->isValid(['custom_layout_update' => 'foo']);
        $messages = $this->validator->getMessages();
        self::assertFalse($result);
        self::assertSame(['oh no'], $messages);
    }
}
