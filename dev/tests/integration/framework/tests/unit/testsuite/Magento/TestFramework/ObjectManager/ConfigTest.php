<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\ObjectManager;

use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCleanMakesExtendReuseConfigurationCachedBeforeClean(): void
    {
        $configuration = ['preferences' => ['Foo' => 'Bar']];
        $savedConfigs = [];
        $requestedKeys = [];
        $cache = $this->createStub(ConfigCacheInterface::class);
        $cache->method('save')
            ->willReturnCallback(function (array $config, $key) use (&$savedConfigs): void {
                $savedConfigs[$key] = $config;
            });
        $cache->method('get')
            ->willReturnCallback(function ($key) use (&$savedConfigs, &$requestedKeys) {
                $requestedKeys[] = $key;
                return $savedConfigs[$key] ?? false;
            });

        $model = $this->createConfig($cache);

        $model->extend($configuration);
        $keysBeforeClean = array_keys($savedConfigs);
        $this->assertCount(1, $keysBeforeClean);

        $model->clean();
        $model->extend($configuration);

        $this->assertCount(
            1,
            $savedConfigs,
            'Configuration must be read from cache after clean() instead of being merged and saved again.'
        );
        $this->assertCount(2, $requestedKeys);
        $this->assertSame(
            $keysBeforeClean[0],
            $requestedKeys[1],
            'Cache key after clean() must match the key used before clean().'
        );
    }

    public function testCleanResetsConfigurationArrays(): void
    {
        $model = new Config();
        $reflection = new \ReflectionClass($model);
        $properties = [];
        $arrayProperties = ['_preferences', '_virtualTypes', '_arguments', '_nonShared', '_mergedArguments'];
        foreach ($arrayProperties as $name) {
            $property = $reflection->getProperty($name);
            $property->setValue($model, [uniqid()]);
            $properties[$name] = $property;
        }

        $model->clean();

        foreach ($properties as $name => $property) {
            $this->assertSame([], $property->getValue($model), $name . ' must be reset by clean().');
        }
    }

    private function createConfig(ConfigCacheInterface $cache): Config
    {
        $model = new Config();
        $model->setCache($cache);
        $serializer = new \ReflectionProperty(\Magento\Framework\ObjectManager\Config\Config::class, 'serializer');
        $serializer->setValue($model, new Json());

        return $model;
    }
}
