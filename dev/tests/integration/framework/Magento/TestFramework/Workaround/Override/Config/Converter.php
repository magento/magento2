<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\TestFramework\Annotation\AdminConfigFixture;
use Magento\TestFramework\Annotation\ConfigFixture;
use Magento\TestFramework\Annotation\DataFixture;
use Magento\TestFramework\Annotation\DataFixtureBeforeTransaction;

/**
 * Converter for tests config
 */
class Converter implements ConverterInterface
{
    private const FIXTURE_TYPES = [
        DataFixture::ANNOTATION,
        DataFixtureBeforeTransaction::ANNOTATION,
        ConfigFixture::ANNOTATION,
        AdminConfigFixture::ANNOTATION,
    ];

    /** @var \DOMXPath */
    private $xpath;

    /**
     * @inheritdoc
     */
    public function convert($source)
    {
        $this->xpath = new \DOMXPath($source);
        $config = [];
        foreach ($this->xpath->query('//test') as $testOverride) {
            $className = ltrim($testOverride->getAttribute('class'), '\\');
            $config[$className] = $this->getTestConfigByFixtureType($testOverride);
            $config[$className] = $this->fillSkipSection($testOverride, $config[$className]);
            foreach ($this->xpath->query('./method', $testOverride) as $method) {
                $methodName = $method->getAttribute('name');
                $config[$className][$methodName] = $config[$className][$methodName] ?? [];
                $config[$className][$methodName] = $this->fillSkipSection($method, $config[$className][$methodName]);

                foreach ($this->xpath->query('./dataSet', $method) as $dataSet) {
                    $setName = $dataSet->getAttribute('name');
                    $config[$className][$methodName][$setName] =   $config[$className][$methodName][$setName] ?? [];
                    $config[$className][$methodName][$setName] = $this->fillSkipSection(
                        $dataSet,
                        $config[$className][$methodName][$setName]
                    );
                }
            }
        }

        return $config;
    }

    /**
     * Fill skip config section
     *
     * @param \DOMElement $node
     * @param array $config
     * @return array
     */
    private function fillSkipSection(\DOMElement $node, array $config): array
    {
        $config['skip'] = $node->getAttribute('skip') === 'true';
        $config['skipMessage'] = $node->getAttribute('skipMessage') ?: null;

        return $config;
    }

    /**
     * Fill test config for all fixture types
     *
     * @param \DOMElement $node
     * @return array
     */
    private function getTestConfigByFixtureType(\DOMElement $node): array
    {
        foreach (self::FIXTURE_TYPES as $fixtureType) {
            $currentTestNodePath = sprintf("//test[@class ='%s']/%s", $node->getAttribute('class'), $fixtureType);
            foreach ($this->xpath->query($currentTestNodePath) as $classDataFixture) {
                $config[$fixtureType][] = $this->fillAttributes($classDataFixture);
            }
            $currentTestMethodsNodePath = sprintf("//test[@class ='%s']/method", $node->getAttribute('class'));
            foreach ($this->xpath->query($currentTestMethodsNodePath, $node) as $method) {
                $methodName = $method->getAttribute('name');
                foreach ($this->xpath->query("./$fixtureType", $method) as $fixture) {
                    $config[$methodName][$fixtureType][] = $this->fillAttributes($fixture);
                }
                foreach ($this->xpath->query('./dataSet', $method) as $dataSet) {
                    $setName = $dataSet->getAttribute('name');

                    foreach ($this->xpath->query("./$fixtureType", $dataSet) as $fixture) {
                        $config[$methodName][$setName][$fixtureType][] = $this->fillAttributes($fixture);
                    }
                }
            }
        }

        return $config ?? [];
    }

    /**
     * Fill node attributes values
     *
     * @param \DOMElement $fixture
     * @return array
     */
    private function fillAttributes(\DOMElement $fixture): array
    {
        $result = [];
        switch ($fixture->nodeName) {
            case DataFixtureBeforeTransaction::ANNOTATION:
            case DataFixture::ANNOTATION:
                $result = $this->fillDataFixtureAttributes($fixture);
                break;
            case ConfigFixture::ANNOTATION:
                $result = $this->fillConfigFixtureAttributes($fixture);
                break;
            case AdminConfigFixture::ANNOTATION:
                $result = $this->fillAdminConfigFixtureAttributes($fixture);
                break;
            default:
                break;
        }

        return $result;
    }

    /**
     * Fill attributes values for dataFixture node types
     *
     * @param \DOMElement $fixture
     * @return array
     */
    private function fillDataFixtureAttributes(\DOMElement $fixture): array
    {
        return [
            'path' => $fixture->getAttribute('path'),
            'newPath' => $fixture->getAttribute('newPath') ?? null,
            'before' => $fixture->getAttribute('before') ?? null,
            'after' => $fixture->getAttribute('after') ?? null,
            'remove' => $fixture->getAttribute('remove') ?: false,
        ];
    }

    /**
     * Fill attributes values for configFixture node types
     *
     * @param \DOMElement $fixture
     * @return array
     */
    private function fillConfigFixtureAttributes(\DOMElement $fixture): array
    {
        return [
            'path' => $fixture->getAttribute('path'),
            'value' => $fixture->getAttribute('value'),
            'newValue' => $fixture->getAttribute('newValue'),
            'scopeType' => $fixture->getAttribute('scopeType'),
            'scopeCode' => $fixture->getAttribute('scopeCode'),
            'remove' => $fixture->getAttribute('remove'),
        ];
    }

    /**
     * Fill attributes values for adminConfigFixture node types
     *
     * @param \DOMElement $fixture
     * @return array
     */
    private function fillAdminConfigFixtureAttributes(\DOMElement $fixture): array
    {
        return [
            'path' => $fixture->getAttribute('path'),
            'value' => $fixture->getAttribute('value'),
            'newValue' => $fixture->getAttribute('newValue'),
            'remove' => $fixture->getAttribute('remove'),
        ];
    }
}
