<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Implementation of the @magentoCache DocBlock annotation
 */
class Cache
{
    /**
     * Original values for cache type states
     *
     * @var array
     */
    private $origValues = [];

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @return void
     */
    public function startTest(\PHPUnit_Framework_TestCase $test)
    {
        $source = $test->getAnnotations();
        if (isset($source['method']['magentoCache'])) {
            $annotations = $source['method']['magentoCache'];
        } elseif (isset($source['class']['magentoCache'])) {
            $annotations = $source['class']['magentoCache'];
        } else {
            return;
        }
        $this->setValues($this->parseValues($annotations, $test), $test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @return void
     */
    public function endTest(\PHPUnit_Framework_TestCase $test)
    {
        if ($this->origValues) {
            $this->setValues($this->origValues, $test);
            $this->origValues = [];
        }
    }

    /**
     * Determines from docblock annotations which cache types to set
     *
     * @param array $annotations
     * @param \PHPUnit_Framework_TestCase $test
     * @return array
     */
    private function parseValues($annotations, \PHPUnit_Framework_TestCase $test)
    {
        $result = [];
        $typeList = self::getTypeList();
        foreach ($annotations as $subject) {
            if (!preg_match('/^([a-z_]+)\s(enabled|disabled)$/', $subject, $matches)) {
                self::fail("Invalid @magentoCache declaration: '{$subject}'", $test);
            }
            list(, $requestedType, $isEnabled) = $matches;
            $isEnabled = $isEnabled == 'enabled' ? 1 : 0;
            if ('all' === $requestedType) {
                $result = [];
                foreach ($typeList->getTypes() as $type) {
                    $result[$type['id']] = $isEnabled;
                }
            } else {
                $result[$requestedType] = $isEnabled;
            }
        }
        return $result;
    }

    /**
     * Sets the values of cache types
     *
     * @param array $values
     * @param \PHPUnit_Framework_TestCase $test
     */
    private function setValues($values, \PHPUnit_Framework_TestCase $test)
    {
        $typeList = self::getTypeList();
        if (!$this->origValues) {
            $this->origValues = [];
            foreach ($typeList->getTypes() as $type => $row) {
                $this->origValues[$type] = $row['status'];
            }
        }
        /** @var \Magento\Framework\App\Cache\StateInterface $states */
        $states = Bootstrap::getInstance()->getObjectManager()->get('Magento\Framework\App\Cache\StateInterface');
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
        return Bootstrap::getInstance()->getObjectManager()->get('Magento\Framework\App\Cache\TypeListInterface');
    }

    /**
     * Fails the test with specified error message
     *
     * @param string $message
     * @param \PHPUnit_Framework_TestCase $test
     * @throws \Exception
     */
    private static function fail($message, \PHPUnit_Framework_TestCase $test)
    {
        $test->fail("{$message} in the test '{$test->toString()}'");
        throw new \Exception('The above line was supposed to throw an exception.');
    }
}
