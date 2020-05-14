<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture\Applier;

/**
 * Class represent data fixtures applying logic
 */
class DataFixture extends Base
{
    /**
     * Replace one fixture according to override configurations
     *
     * @param string $fixture
     * @return string
     */
    public function replace(string $fixture): string
    {
        $replacedFixtures = [];
        foreach ($this->getPrioritizedConfig() as $config) {
            foreach ($config as $testFixture) {
                if (!empty($testFixture['newPath'])) {
                    $replacedFixtures[$testFixture['path']] = $testFixture['newPath'];
                }
            }
        }
        $fixture = $this->replaceFixtures([$fixture], $replacedFixtures);

        return is_array($fixture) ? reset($fixture) : $fixture;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $fixtures): array
    {
        $replacedFixtures = [];
        foreach ($this->getPrioritizedConfig() as $config) {
            foreach ($config as $testFixture) {
                if (empty($testFixture['newPath']) && empty($testFixture['remove'])) {
                    $fixtures = $this->sortFixtures($fixtures, $testFixture);
                }
                if (!empty($testFixture['remove'])) {
                    $fixtures = $this->removeFixtures($fixtures, $testFixture);
                }
                if (!empty($testFixture['newPath'])) {
                    $replacedFixtures[$testFixture['path']] = $testFixture['newPath'];
                }
            }
        }
        $fixtures = $this->replaceFixtures($fixtures, $replacedFixtures);

        return $fixtures;
    }

    /**
     * Replace test fixtures according config
     *
     * @param array $fixtures
     * @param array $replacedFixtures
     * @return array
     */
    private function replaceFixtures(array $fixtures, array $replacedFixtures): array
    {
        foreach ($fixtures as $key => $fixture) {
            if (!empty($replacedFixtures[$fixture])) {
                $fixtures[$key] = $replacedFixtures[$fixture];
            }
        }

        return $fixtures;
    }

    /**
     * Remove fixtures according config
     *
     * @param array $fixtures
     * @param array $attributes
     * @return array
     */
    private function removeFixtures(array $fixtures, array $attributes): array
    {
        $key = array_search($attributes['path'], $fixtures);
        if ($key || $key === 0) {
            unset($fixtures[$key]);
        }

        return $fixtures;
    }

    /**
     * Sort fixtures according config
     *
     * @param array $fixtures
     * @param array $attributes
     * @return array
     */
    private function sortFixtures(array $fixtures, array $attributes): array
    {
        $beforeFixtures = [];
        $afterFixtures = [];
        if (!empty($attributes['before'])) {
            $offset = array_search($attributes['before'], $fixtures);
            if ($attributes['before'] === '-' || $offset === 0) {
                $beforeFixtures[] = $attributes['path'];
            } else {
                $fixtures = $this->insertFixture($fixtures, $attributes['path'], $offset);
            }
        }
        if (!empty($attributes['after'])) {
            if ($attributes['after'] === '-') {
                $afterFixtures[] = $attributes['path'];
            } else {
                $offset = array_search($attributes['after'], $fixtures);
                $fixtures = $this->insertFixture($fixtures, $attributes['path'], $offset + 1);
            }
        } elseif (empty($attributes['before'])) {
            $fixtures[] = $attributes['path'];
        }

        return array_merge($beforeFixtures, $fixtures, $afterFixtures);
    }

    /**
     * Insert fixture into position
     *
     * @param array $fixtures
     * @param string $fixture
     * @param int $position
     * @return array
     */
    private function insertFixture(array $fixtures, string $fixture, int $position): array
    {
        return array_merge(
            array_slice($fixtures, 0, $position),
            [$fixture],
            array_slice($fixtures, $position)
        );
    }
}
