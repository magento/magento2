<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\DataObject;
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
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(
        private Registry $registry,
        private DataFixtureFactory $dataFixtureFactory,
        private ScopeSwitcherInterface $scopeSwitcher,
        private ScopeResolverPool $scopeResolverPool,
    ) {
    }

    /**
     * Applies data fixture and returns the result.
     *
     * @param array $fixture
     * @return DataObject|null
     * @throws \Exception
     */
    public function apply(array $fixture): ?DataObject
    {
        $data = $this->resolveVariables($fixture['data'] ?? []);
        $factory = $this->dataFixtureFactory->create($fixture['factory']);
        if (isset($fixture['scope'])) {
            $scope = $this->resolveScope($fixture['scope'], $fixture['scopeType']);
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
     * Resolve scope by name and type.
     *
     * @param string $scopeName
     * @param string $scopeType
     * @return ScopeInterface
     * @throws \InvalidArgumentException
     */
    private function resolveScope(string $scopeName, string $scopeType): ScopeInterface
    {
        $scope = DataFixtureStorageManager::getStorage()->get($scopeName);
        if (null === $scope) {
            $scopeIdentifier = $this->resolveValue($scopeName);
            if (null !== $scopeIdentifier) {
                try {
                    $scopeResolver = $this->scopeResolverPool->get($scopeType);
                } catch (\Exception) {
                    $msg = sprintf('"%s" is not valid scope type.', $scopeType);
                    throw new \InvalidArgumentException($msg);
                }
                try {
                    $scope = $scopeResolver->getScope($scopeIdentifier);
                } catch (\Exception) {
                    $msg = sprintf('"%s" is not valid scope for "%s" scope type.', $scopeIdentifier, $scopeType);
                    throw new \InvalidArgumentException($msg);
                }
            }
        }
        if (!$scope instanceof ScopeInterface) {
            $msg = sprintf('"%s" is not valid scope.', $scopeName);
            throw new \InvalidArgumentException($msg);
        }

        return $scope;
    }

    /**
     * Replace fixture reference by its value.
     *
     * @param string $value
     * @return string|null
     */
    private function resolveValue(string $value): ?string
    {
        return $this->getParser($value)?->__invoke($value);
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
     * @throws \Exception
     */
    private function resolveVariables(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->resolveVariables($value);
            } else {
                if (is_string($value)) {
                    $parser = $this->getParser($value);
                    if ($parser) {
                        $data[$key] = $parser($value);
                    }
                }
            }

            if (is_string($key)) {
                $newKey = $this->resolveValue($key);
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
     * @return \Closure|null
     */
    private function getParser(string $data): ?\Closure
    {
        // Check if entire string is a single placeholder
        if (preg_match('/^\$\w+(\.\w+)?\$$/', $data)) {
            return $this->resolveSinglePlaceholder(...);
        }

        // Check if string contains one or more placeholders, for multi value support
        if (preg_match('/\$\w+(\.\w+)?\$/', $data)) {
            return $this->resolveMultiplePlaceholders(...);
        }

        return null;
    }

    /**
     * Resolve a single fixture placeholder
     *
     * @param string $data
     * @return DataObject|mixed
     * @throws \InvalidArgumentException
     */
    private function resolveSinglePlaceholder(string $data)
    {
        list($fixtureName, $attribute) = array_pad(explode('.', trim($data, '$')), 2, null);
        $fixtureData = $this->getFixtureData($fixtureName, $data);
        return $this->extractValue($fixtureData, $attribute);
    }

    /**
     * Resolve multiple fixture placeholders in a string
     *
     * @param string $data
     * @return string|false
     */
    private function resolveMultiplePlaceholders(string $data)
    {
        $resolved = preg_replace_callback(
            '/\$(\w+)(\.\w+)?\$/',
            function ($matches) {
                return $this->replacePlaceholder($matches);
            },
            $data
        );
        return $resolved !== $data ? $resolved : false;
    }

    /**
     * Replace a single placeholder match
     *
     * @param array $matches
     * @return string|mixed
     * @throws \InvalidArgumentException
     */
    private function replacePlaceholder(array $matches)
    {
        $fixtureName = $matches[1];
        $attribute = isset($matches[2]) ? ltrim($matches[2], '.') : null;
        $reference = "\${$fixtureName}" . ($attribute ? ".{$attribute}" : '') . "\$";
        $fixtureData = $this->getFixtureData($fixtureName, $reference);
        $value = $this->extractValue($fixtureData, $attribute);
        return is_scalar($value) ? (string)$value : $value;
    }

    /**
     * Get fixture data from storage
     *
     * @param string $fixtureName
     * @param string $reference
     * @return DataObject
     * @throws \InvalidArgumentException
     */
    private function getFixtureData(string $fixtureName, string $reference): DataObject
    {
        $fixtureData = DataFixtureStorageManager::getStorage()->get($fixtureName);
        if (!$fixtureData) {
            throw new \InvalidArgumentException("Unable to resolve fixture reference '{$reference}'");
        }
        return $fixtureData;
    }

    /**
     * Extract value from fixture data
     *
     * @param DataObject $fixtureData
     * @param string|null $attribute
     * @return DataObject|mixed
     */
    private function extractValue(DataObject $fixtureData, ?string $attribute)
    {
        return $attribute !== null ? $fixtureData->getDataUsingMethod($attribute) : $fixtureData;
    }
}
