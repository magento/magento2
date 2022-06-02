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

class IndexerDimensionMode implements ParserInterface
{
    /**
     * @var string
     */
    private const ANNOTATION = 'magentoIndexerDimensionMode';

    /**
     * @inheritdoc
     */
    public function parse(TestCase $test, string $scope): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $values = [];

        foreach ($annotations[$scope][self::ANNOTATION] ?? [] as $value) {
            $args = explode(' ', $value);
            if (count($args) !== 2) {
                throw new LocalizedException(
                    __(
                        "Invalid annotation format: @%1 %2. The valid format is: @%1 <indexer> <dimension>.",
                        self::ANNOTATION,
                        $value
                    )
                );
            }
            $values[] = ['indexer' => $args[0], 'dimension' => $args[1]];
        }

        return $values;
    }
}
