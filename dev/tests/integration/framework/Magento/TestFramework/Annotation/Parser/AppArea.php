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

class AppArea implements ParserInterface
{
    /**
     * @var string
     */
    private const ANNOTATION = 'magentoAppArea';

    /**
     * List of allowed areas.
     *
     * @var array
     */
    private const ALLOWED_AREAS = [
        \Magento\Framework\App\Area::AREA_GLOBAL,
        \Magento\Framework\App\Area::AREA_ADMINHTML,
        \Magento\Framework\App\Area::AREA_FRONTEND,
        \Magento\Framework\App\Area::AREA_WEBAPI_REST,
        \Magento\Framework\App\Area::AREA_WEBAPI_SOAP,
        \Magento\Framework\App\Area::AREA_CRONTAB,
        \Magento\Framework\App\Area::AREA_GRAPHQL
    ];

    /**
     * @inheritdoc
     */
    public function parse(TestCase $test, string $scope): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $values = [];

        foreach ($annotations[$scope][self::ANNOTATION] ?? [] as $value) {
            if (!in_array($value, self::ALLOWED_AREAS, true)) {
                throw new LocalizedException(
                    __(
                        "Invalid annotation format: @%1 %2. The valid format is: @%1 [%3].",
                        self::ANNOTATION,
                        $value,
                        implode('|', self::ALLOWED_AREAS)
                    )
                );
            }
            $values[] = ['area' => $value];
        }

        return $values;
    }
}
