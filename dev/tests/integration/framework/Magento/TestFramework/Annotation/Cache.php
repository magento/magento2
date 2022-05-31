<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Parser\Cache as CacheFixtureParser;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoCache DocBlock annotation
 */
class Cache
{
    public const ANNOTATION = 'magentoCache';
    /**
     * Original values for cache type states
     *
     * @var array
     */
    private $origValues = [];

    /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     * @return void
     */
    public function startTest(TestCase $test)
    {
        $statusList = array_merge(
            $this->getFixturesFromCacheAttribute($test, ParserInterface::SCOPE_METHOD),
            $this->getFixturesFromCacheAnnotation($test, ParserInterface::SCOPE_METHOD)
        );
        if (!$statusList) {
            $statusList = array_merge(
                $this->getFixturesFromCacheAttribute($test, ParserInterface::SCOPE_CLASS),
                $this->getFixturesFromCacheAnnotation($test, ParserInterface::SCOPE_CLASS)
            );
        }

        if ($statusList) {
            $values = [];
            $typeList = self::getTypeList();
            foreach ($statusList as $cache) {
                if ('all' === $cache['type']) {
                    foreach ($typeList->getTypes() as $type) {
                        $values[$type['id']] = $cache['status'];
                    }
                } else {
                    $values[$cache['type']] = $cache['status'];
                }
            }
            $this->setValues($values, $test);
        }
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     * @return void
     */
    public function endTest(TestCase $test)
    {
        if ($this->origValues) {
            $this->setValues($this->origValues, $test);
            $this->origValues = [];
        }
    }

    /**
     * Sets the values of cache types
     *
     * @param array $values
     * @param TestCase $test
     */
    private function setValues($values, TestCase $test)
    {
        $typeList = self::getTypeList();
        if (!$this->origValues) {
            $this->origValues = [];
            foreach ($typeList->getTypes() as $type => $row) {
                $this->origValues[$type] = $row['status'];
            }
        }
        /** @var \Magento\Framework\App\Cache\StateInterface $states */
        $states = Bootstrap::getInstance()->getObjectManager()->get(\Magento\Framework\App\Cache\StateInterface::class);
        foreach ($values as $type => $isEnabled) {
            if (!isset($this->origValues[$type])) {
                self::fail("Unknown cache type specified: '{$type}' in @magentoCache", $test);
            }
            $states->setEnabled($type, $isEnabled);
        }
    }

    /**
     * Getter for cache types list
     *
     * @return \Magento\Framework\App\Cache\TypeListInterface
     */
    private static function getTypeList()
    {
        return Bootstrap::getInstance()->getObjectManager()->get(\Magento\Framework\App\Cache\TypeListInterface::class);
    }

    /**
     * Fails the test with specified error message
     *
     * @param string $message
     * @param TestCase $test
     * @throws \Exception
     */
    private static function fail($message, TestCase $test)
    {
        $test->fail("{$message} in the test '{$test->toString()}'");
        throw new \Exception('The above line was supposed to throw an exception.');
    }

    /**
     * Returns cache fixtures defined using Cache annotation
     *
     * @param TestCase $test
     * @param string $scope
     * @return array
     * @throws \Exception
     */
    private function getFixturesFromCacheAnnotation(TestCase $test, string $scope): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $configs = [];

        foreach ($annotations[$scope][self::ANNOTATION] ?? [] as $annotation) {
            if (!preg_match('/^([a-z_]+)\s(enabled|disabled)$/', $annotation, $matches)) {
                self::fail("Invalid @magentoCache declaration: '{$annotation}'", $test);
            }
            $configs[] = ['type' => $matches[1], 'status' => $matches[2] === 'enabled'];
        }

        return $configs;
    }

    /**
     * Returns cache fixtures defined using Cache attribute
     *
     * @param TestCase $test
     * @param string $scope
     * @return array
     * @throws LocalizedException
     */
    private function getFixturesFromCacheAttribute(TestCase $test, string $scope): array
    {
        return Bootstrap::getObjectManager()->create(CacheFixtureParser::class)->parse($test, $scope);
    }
}
