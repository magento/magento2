<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

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
     * Return data from fixture data provider
     *
     * @param TestCase $test
     * @return array
     */
    public function getDataProvider(TestCase $test): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $dataProvider = $annotations['method'][self::ANNOTATION] ?? $annotations['class'][self::ANNOTATION] ?? [];
        $data = [];
        if (isset($dataProvider[0])) {
            if (is_callable([$test, $dataProvider[0]])) {
                $data = call_user_func([$test, $dataProvider[0]]);
            } elseif (is_callable($dataProvider[0])) {
                $data = call_user_func($dataProvider[0]);
            } else {
                throw new Exception('Fixture dataprovider must be a callable');
            }
        }

        return $data;
    }
}
