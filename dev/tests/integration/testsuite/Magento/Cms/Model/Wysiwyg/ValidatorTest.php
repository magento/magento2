<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg;

use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verifies heading validation through the merged dependency injection configuration.
 */
class ValidatorTest extends TestCase
{
    /**
     * H6 must validate even when invalid HTML would throw instead of emitting a warning.
     */
    #[Config('cms/wysiwyg/force_valid', '1')]
    #[DataProvider('validatorProvider')]
    public function testAllowsH6(string $validatorType): void
    {
        $validator = Bootstrap::getObjectManager()->get($validatorType);
        $this->assertInstanceOf(WYSIWYGValidatorInterface::class, $validator);
        $validator->validate('<h6>x</h6>');
    }

    /**
     * Provides the public interface and the CMS-specific validator entry point.
     *
     * @return array<string, array{string}>
     */
    public static function validatorProvider(): array
    {
        return [
            'interface' => [WYSIWYGValidatorInterface::class],
            'cms' => [Validator::class]
        ];
    }
}
