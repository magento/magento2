<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Event\Magento;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Metadata\MetadataCollection;

class Dataset extends DataObject
{
    /**
     * @var bool
     */
    private bool $deferred;

    /**
     * @param string $testClass
     * @param string $testMethod
     * @param int|string $name
     * @param array|null $data
     */
    public function __construct(
        private readonly string $testClass,
        private readonly string $testMethod,
        private readonly int|string $name,
        ?array $data = null
    ) {
        $this->deferred = $data === null;
        parent::__construct($data ?? []);
    }

    /**
     * @inheritDoc
     */
    public function getData($key = '', $index = null)
    {
        if ($this->deferred) {
            $this->setData($this->getDataFromDataProvider());
            $this->deferred = false;
        }
        
        if ($key !== '' && !$this->hasData($key)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Key '%s' does not exist in the %s. Available keys: %s",
                    $key,
                    is_int($this->name)
                        ? sprintf('data set #%s', $this->name)
                        : sprintf('data set \'%s\'', $this->name),
                    implode(', ', array_keys($this->getData()))
                )
            );
        }
        return parent::getData($key, $index);
    }
    /**
     * Returns the dataset, which is either the data from the DataProvider or TestWith.
     *
     * @return array
     */
    private function getDataFromDataProvider(): array
    {
        $testMethod = $this->getTestMethodInstance();
        $dataSetName = $testMethod->testData()->dataFromDataProvider()->dataSetName();
        $dataProvider = $testMethod->metadata()->isDataProvider();
        if ($dataProvider->count() > 0) {
            $data = $this->getDataProvidedByMethods($dataProvider);
            if (array_key_exists($dataSetName, $data)) {
                return $data[$dataSetName];
            }
        }
        foreach ($testMethod->metadata()->isTestWith() as $i => $testWith) {
            $key = $testWith->hasName() ? $testWith->name() : $i;
            if ($key === $dataSetName) {
                return $testWith->data();
            }
        }
        return [];
    }

    /**
     * Returns the data provided by the DataProvider methods.
     *
     * @param MetadataCollection $dataProviders
     * @return array
     */
    private function getDataProvidedByMethods(MetadataCollection $dataProviders): array
    {
        $result = [];
        foreach ($dataProviders as $dataProvider) {
            $className  = $dataProvider->className();
            $methodName = $dataProvider->methodName();
            $data = $className::$methodName();
            foreach ($data as $key => $dataSet) {
                if (is_int($key)) {
                    $result[] = $dataSet;
                } else {
                    $result[$key] = $dataSet;
                }
            }
        }
        return $result;
    }

    /**
     * Returns the current test method instance.
     *
     * @return TestMethod
     */
    private function getTestMethodInstance(): TestMethod
    {
        $eventObject = Magento::getCurrentEventObject();
        if (!$eventObject instanceof PreparationStarted) {
            throw new \LogicException(
                sprintf(
                    'Current event object is not instance of %s, but %s',
                    PreparationStarted::class,
                    is_object($eventObject) ? get_class($eventObject) : gettype($eventObject)
                )
            );
        }
        $testMethod = $eventObject->test();
        if (!$testMethod instanceof TestMethod) {
            throw new \LogicException(
                sprintf(
                    'Current test method is not instance of %s, but %s',
                    TestMethod::class,
                    get_class($testMethod)
                )
            );
        }
        if ($testMethod->className() !== $this->testClass || $testMethod->methodName() !== $this->testMethod) {
            throw new \LogicException(
                sprintf(
                    'Current test method %s::%s does not match the dataset definition %s::%s',
                    $testMethod->className(),
                    $testMethod->methodName(),
                    $this->testClass,
                    $this->testMethod
                )
            );
        }
        return $testMethod;
    }
}
