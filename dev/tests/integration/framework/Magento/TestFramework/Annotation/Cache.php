<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
        $statusList = [];
        try {
            $statusList = $this->parse($test);
        } catch (\Throwable $exception) {
            ExceptionHandler::handle(
                'Unable to parse fixtures',
                $exception,
                $test
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
                ExceptionHandler::handle(
                    "Unknown cache type specified: '{$type}' in @magentoCache",
                    test: $test
                );
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
     * Returns Cache fixtures configuration
     *
     * @param TestCase $test
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function parse(TestCase $test): array
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
        return $parsers->parse($test, ParserInterface::SCOPE_METHOD)
            ?: $parsers->parse($test, ParserInterface::SCOPE_CLASS);
    }
}
