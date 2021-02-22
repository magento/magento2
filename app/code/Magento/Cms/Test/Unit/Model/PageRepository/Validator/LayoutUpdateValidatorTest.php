<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\PageRepository\Validator;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\PageRepository\Validator\LayoutUpdateValidator;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;
use Magento\Framework\View\Model\Layout\Update\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for the layout update validator
 */
class LayoutUpdateValidatorTest extends TestCase
{
    /**
     * @var Validator|MockObject
     */
    private $layoutValidator;

    /**
     * @var LayoutUpdateValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $layoutValidatorFactory = $this->createMock(ValidatorFactory::class);
        $this->layoutValidator = $this->createMock(Validator::class);
        $layoutValidatorState = $this->getMockForAbstractClass(ValidationStateInterface::class);

        $layoutValidatorFactory
            ->method('create')
            ->with(['validationState' => $layoutValidatorState])
            ->willReturn($this->layoutValidator);

        $this->validator = new LayoutUpdateValidator($layoutValidatorFactory, $layoutValidatorState);
    }

    /**
     * @dataProvider validationSetDataProvider
     */
    public function testValidate($data, $expectedExceptionMessage, $layoutValidatorException, $isLayoutValid = false)
    {
        if ($expectedExceptionMessage) {
            $this->expectException(LocalizedException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        if ($layoutValidatorException) {
            $this->layoutValidator
                ->method('isValid')
                ->with($data['getLayoutUpdateXml'] ?? $data['getCustomLayoutUpdateXml'])
                ->willThrowException($layoutValidatorException);
        } elseif (!empty($data['getLayoutUpdateXml'])) {
            $this->layoutValidator
                ->method('isValid')
                ->with($data['getLayoutUpdateXml'])
                ->willReturn($isLayoutValid);
        } elseif (!empty($data['getCustomLayoutUpdateXml'])) {
            $this->layoutValidator
                ->method('isValid')
                ->with($data['getCustomLayoutUpdateXml'])
                ->willReturn($isLayoutValid);
        }

        $page = $this->getMockForAbstractClass(PageInterface::class);
        foreach ($data as $method => $value) {
            $page
                ->method($method)
                ->willReturn($value);
        }

        self::assertNull($this->validator->validate($page));
    }

    public function validationSetDataProvider()
    {
        $layoutError = 'Layout update is invalid';
        $customLayoutError = 'Custom layout update is invalid';
        $validationException = new ValidationException('Invalid format');
        $schemaException = new ValidationSchemaException(__('Invalid format'));

        return [
            [['getTitle' => ''], 'Required field "title" is empty.', null],
            [['getTitle' => null], 'Required field "title" is empty.', null],
            [['getTitle' => false], 'Required field "title" is empty.', null],
            [['getTitle' => 0], 'Required field "title" is empty.', null],
            [['getTitle' => '0'], 'Required field "title" is empty.', null],
            [['getTitle' => []], 'Required field "title" is empty.', null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => ''], null, null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => null], null, null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => false], null, null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => 0], null, null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => '0'], null, null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => []], null, null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => 'foo'], $layoutError, null],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => 'foo'], $layoutError, $validationException],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => 'foo'], $layoutError, $schemaException],
            [['getTitle' => 'foo', 'getLayoutUpdateXml' => 'foo'], null, null, true],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => ''], null, null],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => null], null, null],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => false], null, null],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => 0], null, null],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => '0'], null, null],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => []], null, null],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => 'foo'], $customLayoutError, null],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => 'foo'], $customLayoutError, $validationException],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => 'foo'], $customLayoutError, $schemaException],
            [['getTitle' => 'foo', 'getCustomLayoutUpdateXml' => 'foo'], null, null, true],
        ];
    }
}
