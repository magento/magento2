<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

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
        $objectManager = Bootstrap::getObjectManager();
        $parsers = $objectManager
            ->create(
                \Magento\TestFramework\Annotation\Parser\Composite::class,
                [
                    'parsers' => [
                        $objectManager->get(\Magento\TestFramework\Annotation\Parser\Cache::class),
                        $objectManager->get(\Magento\TestFramework\Fixture\Parser\Cache::class)
                    ]
                ]
            );
        $statusList = $parsers->parse($test, ParserInterface::SCOPE_METHOD)
            ?: $parsers->parse($test, ParserInterface::SCOPE_CLASS);

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
}
