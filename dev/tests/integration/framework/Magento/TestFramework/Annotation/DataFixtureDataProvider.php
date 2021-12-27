<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Data fixture data provider annotation
 */
class DataFixtureDataProvider
{
    /**
     * Annotation name
     */
    public const ANNOTATION = 'magentoDataFixtureDataProvider';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Return data from fixture data provider
     *
     * @param TestCase $test
     * @return array
     */
    public function getDataProvider(TestCase $test): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $dataProviders = array_merge(
            $annotations['class'][self::ANNOTATION] ?? [],
            $annotations['method'][self::ANNOTATION] ?? []
        );
        $result = [];
        foreach (array_reverse($dataProviders) as $dataProvider) {
            if (isset($dataProvider)) {
                if (is_callable([$test, $dataProvider])) {
                    $data = $test->$dataProvider();
                } elseif (is_callable($dataProvider)) {
                    $data = $dataProvider();
                } else {
                    try {
                        $data = $this->serializer->unserialize($dataProvider);
                    } catch (\InvalidArgumentException $exception) {
                        throw new Exception('Fixture data provider must be a callable or valid JSON');
                    }
                }
                $result += $data;
            }

        }

        return $result;
    }
}
