<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Wysiwyg;

use Magento\Cms\Model\Wysiwyg\Validator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\Factory as MessageFactory;

class ValidatorTest extends TestCase
{
    /**
     * Validation cases.
     *
     * @return array
     */
    public static function getValidationCases(): array
    {
        return [
            'invalid-exception' => [true, new ValidationException(__('Invalid html')), true, false],
            'invalid-warning' => [false, new \RuntimeException('Invalid html'), false, true],
            'valid' => [false, null, false, false]
        ];
    }

    /**
     * Test validation.
     *
     * @param bool $isFlagSet
     * @param \Throwable|null $thrown
     * @param bool $exceptionThrown
     * @param bool $warned
     * @dataProvider getValidationCases
     */
    public function testValidate(bool $isFlagSet, ?\Throwable $thrown, bool $exceptionThrown, bool $warned): void
    {
        $actuallyWarned = false;

        $messageFactoryMock = $this->createMock(MessageFactory::class);
        $messageFactoryMock->method('create')
            ->willReturnCallback(
                function () {
                    return $this->getMockForAbstractClass(MessageInterface::class);
                }
            );
        $configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $configMock->method('isSetFlag')
            ->with(Validator::CONFIG_PATH_THROW_EXCEPTION)
            ->willReturn($isFlagSet);

        $backendMock = $this->getMockForAbstractClass(WYSIWYGValidatorInterface::class);
        if ($thrown) {
            $backendMock->method('validate')->willThrowException($thrown);
        }

        $messagesMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $messagesMock->method('addUniqueMessages')
            ->willReturnCallback(
                function () use (&$actuallyWarned): void {
                    $actuallyWarned = true;
                }
            );

        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $validator = new Validator($backendMock, $messagesMock, $configMock, $loggerMock, $messageFactoryMock);
        try {
            $validator->validate('content');
            $actuallyThrown = false;
        } catch (\Throwable $exception) {
            $actuallyThrown = true;
        }
        $this->assertEquals($exceptionThrown, $actuallyThrown);
        $this->assertEquals($warned, $actuallyWarned);
    }
}
