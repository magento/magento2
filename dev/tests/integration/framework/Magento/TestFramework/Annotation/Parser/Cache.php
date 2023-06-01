<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation\Parser;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Annotation\TestCaseAnnotation;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

class Cache implements ParserInterface
{
    /**
     * @var string
     */
    private const ANNOTATION = 'magentoCache';

    /**
     * @inheritdoc
     */
    public function parse(TestCase $test, string $scope): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $values = [];

        foreach ($annotations[$scope][self::ANNOTATION] ?? [] as $value) {
            if (!preg_match('/^([a-z_]+)\s(enabled|disabled)$/', $value, $matches)) {
                throw new LocalizedException(
                    __(
                        "Invalid annotation format: @%1 %2. The valid format is: @%1 [<type>|all] [enabled|disabled].",
                        self::ANNOTATION,
                        $value
                    )
                );
            }
            $values[] = ['type' => $matches[1], 'status' => $matches[2] === 'enabled'];
        }

        return $values;
    }
}
