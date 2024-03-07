<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Fixture\DataFixtureFactory;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\ScopeSwitcherInterface;

/**
 * Apply and revert data fixtures
 */
class DataFixtureSetup
{
    /**
     * @param Registry $registry
     * @param DataFixtureFactory $dataFixtureFactory
     * @param ScopeSwitcherInterface $scopeSwitcher
     */
    public function __construct(
        private Registry $registry,
        private DataFixtureFactory $dataFixtureFactory,
        private ScopeSwitcherInterface $scopeSwitcher
    ) {
    }

    /**
     * Applies data fixture and returns the result.
     *
     * @param array $fixture
     * @return DataObject|null
     * @throws LocalizedException
     */
    public function apply(array $fixture): ?DataObject
    {
        $data = $this->resolveVariables($fixture['data'] ?? []);
        $factory = $this->dataFixtureFactory->create($fixture['factory']);
        if (isset($fixture['scope'])) {
            $scope = DataFixtureStorageManager::getStorage()->get($fixture['scope']);
            $fromScope = $this->scopeSwitcher->switch($scope);
            try {
                $result = $factory->apply($data);
            } finally {
                $this->scopeSwitcher->switch($fromScope);
            }
        } else {
            $result = $factory->apply($data);
        }

        if ($result !== null && !empty($fixture['name'])) {
            DataFixtureStorageManager::getStorage()->persist(
                $fixture['name'],
                $result
            );
        }

        return $result;
    }

    /**
     * Revert data fixture.
     *
     * @param array $fixture
     */
    public function revert(array $fixture): void
    {
        $isSecureArea = $this->registry->registry('isSecureArea');
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        try {
            $factory = $this->dataFixtureFactory->create($fixture['factory']);
            if ($factory instanceof RevertibleDataFixtureInterface) {
                $factory->revert($fixture['result'] ?? new DataObject());
            }
        } catch (NoSuchEntityException $exception) {
            //ignore
        } finally {
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', $isSecureArea);
        }
    }

    /**
     * Replace fixtures references in the data by their value
     *
     * Supported formats:
     * - $fixture$
     * - $fixture.attribute$
     *
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function resolveVariables(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->resolveVariables($value);
            } else {
                if (is_string($value)) {
                    $value = $this->parseFixtureKeyValue($value);
                    if ($value) {
                        $data[$key] = $value;
                    }
                }
            }

            if (is_string($key)) {
                $newKey = $this->parseFixtureKeyValue($key);
                if (is_string($newKey)) {
                    $value = $data[$key];
                    unset($data[$key]);
                    $data[$newKey] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Parse either key or value of the fixture data
     *
     * @param string $data
     * @return DataObject|mixed|void
     * @throws LocalizedException
     */
    private function parseFixtureKeyValue(string $data)
    {
        if (preg_match('/^\$\w+(\.\w+)?\$$/', $data)) {
            list($fixtureName, $attribute) = array_pad(explode('.', trim($data, '$')), 2, null);
            $fixtureData = DataFixtureStorageManager::getStorage()->get($fixtureName);
            if (!$fixtureData) {
                throw new \InvalidArgumentException("Unable to resolve fixture reference '$data'");
            }
            return $attribute ? $fixtureData->getDataUsingMethod($attribute) : $fixtureData;
        }
        return false;
    }
}
