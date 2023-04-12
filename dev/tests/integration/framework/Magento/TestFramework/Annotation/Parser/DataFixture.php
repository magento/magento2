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

class DataFixture implements ParserInterface
{
    /**
     * @var string
     */
    private string $annotation;

    /**
     * @param string $annotation
     */
    public function __construct(
        string $annotation
    ) {
        $this->annotation = $annotation;
    }

    /**
     * @inheritdoc
     */
    public function parse(TestCase $test, string $scope): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $fixtures = [];
        foreach ($annotations[$scope][$this->annotation] ?? [] as $fixture) {
            list($factory, $extraParams) = array_pad(array_values(array_filter(explode(' ', $fixture, 2))), 2, '');
            if (str_contains($factory, '\\') && !is_callable($factory)) {
                if (class_exists($factory)) {
                    throw new LocalizedException(
                        __(
                            'Support for fixture class %1 in data fixture annotation was dropped' .
                            ' in favor to data fixture attribute. Please see documentation',
                            $factory
                        )
                    );
                }
                throw new LocalizedException(
                    __(
                        'Data Fixture annotation expects argument #1 to be the fixture filename or a callable,' .
                        ' %1 given',
                        $factory
                    )
                );
            }
            if ($extraParams) {
                throw new LocalizedException(
                    __(
                        'Data Fixture annotation expects only one argument: %1',
                        $fixture
                    )
                );
            }

            $fixtures[] = [
                'name' => null,
                'factory' => $factory,
                'data' => [],
            ];
        }
        return $fixtures;
    }
}
