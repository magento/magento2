<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture\Applier;

/**
 * Class represent config fixtures applying logic
 */
class ConfigFixture extends Base
{
    /**
     * @inheritdoc
     */
    public function apply(array $fixtures): array
    {
        foreach ($this->getPrioritizedConfig() as $config) {
            foreach ($config as $testFixture) {
                if (empty($testFixture['newValue']) && empty($testFixture['remove'])) {
                    $fixtures[] = $this->initConfigFixture($testFixture);
                }
                if (!empty($testFixture['remove'])) {
                    $fixtures = $this->removeFixtures($fixtures, $testFixture);
                }
                if (!empty($testFixture['newValue'])) {
                    $fixtures = $this->replaceFixtures($fixtures, $testFixture);
                }
            }
        }

        return $fixtures;
    }

    /**
     * Check is annotation fixture match override node
     *
     * @param array $attributes
     * @param string $currentFixture
     * @return bool
     */
    protected function isFixtureMatch(array $attributes, string $currentFixture): bool
    {
        $pattern = sprintf(
            '/\s*(%s_%s?|default\/)\s*%s\s/i',
            $attributes['scopeCode'],
            $attributes['scopeType'],
            str_replace('/', '\/', $attributes['path'])
        );

        return (bool)preg_match($pattern, $currentFixture);
    }

    /**
     * Create config annotation string
     *
     * @param array $attributes
     * @return string
     */
    protected function initConfigFixture(array $attributes): string
    {
        $value = !empty($attributes['newValue']) ? $attributes['newValue'] : $attributes['value'];

        return $attributes['scopeType'] === 'default'
            ? sprintf('%s/%s %s', $attributes['scopeType'], $attributes['path'], $value)
            : sprintf('%s_%s %s %s', $attributes['scopeCode'], $attributes['scopeType'], $attributes['path'], $value);
    }

    /**
     * Remove fixtures from config fixtures list according to overrides
     *
     * @param array $fixtures
     * @param array $attributes
     * @return array
     */
    private function removeFixtures(array $fixtures, array $attributes): array
    {
        foreach ($fixtures as $key => $currentFixture) {
            if ($this->isFixtureMatch($attributes, $currentFixture)) {
                unset($fixtures[$key]);
            }
        }

        return $fixtures;
    }

    /**
     * Replace config fixture according to overrides
     *
     * @param array $fixtures
     * @param array $attributes
     * @return array
     */
    private function replaceFixtures(array $fixtures, array $attributes): array
    {
        foreach ($fixtures as $key => $currentFixture) {
            if ($this->isFixtureMatch($attributes, $currentFixture)) {
                $fixtures[$key] = $this->initConfigFixture($attributes);
            }
        }

        return $fixtures;
    }
}
