<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation\Parser;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Annotation\TestCaseAnnotation;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

class AppIsolation implements ParserInterface
{
    /**
     * @var string
     */
    private const ANNOTATION = 'magentoAppIsolation';

    /**
     * @inheritdoc
     */
    public function parse(TestCase $test, string $scope): array
    {
        try {
            $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        } catch (\Throwable $e) {
            return [];
        }
        $values = [];

        foreach ($annotations[$scope][self::ANNOTATION] ?? [] as $value) {
            if (!in_array($value, ['enabled', 'disabled'])) {
                throw new LocalizedException(
                    __(
                        "Invalid annotation format: @%1 %2. The valid format is: @%1 enabled|disabled.",
                        self::ANNOTATION,
                        $value
                    )
                );
            }
            $values[] = ['enabled' => $value === 'enabled'];
        }

        return $values;
    }
}
