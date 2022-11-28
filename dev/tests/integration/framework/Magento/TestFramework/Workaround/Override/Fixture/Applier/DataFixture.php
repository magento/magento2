<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture\Applier;

use Magento\Framework\Exception\LocalizedException;

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
        $fixture = $this->replaceFixtures([$this->getFixtureAsArray($fixture)], $replacedFixtures);

        return reset($fixture)['factory'];
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
        if ($replacedFixtures) {
            foreach ($fixtures as $key => $fixture) {
                if (!empty($replacedFixtures[$fixture['factory']])) {
                    $fixtures[$key] = $this->getFixtureAsArray($replacedFixtures[$fixture['factory']]);
                }
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
        try {
            $key = $this->getFixturePosition($attributes['path'], $fixtures);
            unset($fixtures[$key]);
        } catch (\Throwable $exception) {
            //ignore exception
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
            $offset = $attributes['before'] === '-' ? 0 : $this->getFixturePosition($attributes['before'], $fixtures);
            if ($offset === 0) {
                $beforeFixtures[] = $this->getFixtureAsArray($attributes['path']);
            } else {
                $fixtures = $this->insertFixture($fixtures, $attributes['path'], $offset);
            }
        }
        if (!empty($attributes['after'])) {
            if ($attributes['after'] === '-') {
                $afterFixtures[] = $this->getFixtureAsArray($attributes['path']);
            } else {
                $offset = $this->getFixturePosition($attributes['after'], $fixtures);
                $fixtures = $this->insertFixture($fixtures, $attributes['path'], $offset + 1);
            }
        } elseif (empty($attributes['before'])) {
            $fixtures[] = $this->getFixtureAsArray($attributes['path']);
        }

        return array_merge($beforeFixtures, $fixtures, $afterFixtures);
    }

    /**
     * Get fixture position in added fixtures list
     *
     * @param string $fixtureToFind
     * @param array $existingFixtures
     * @return int
     * @throws LocalizedException if fixture which have to be found does not exist in added fixtures list
     */
    private function getFixturePosition(string $fixtureToFind, array $existingFixtures): int
    {
        $offset = false;
        foreach ($existingFixtures as $key => $fixture) {
            if ($fixture['factory'] === $fixtureToFind) {
                $offset = $key;
                break;
            }
        }
        if ($offset === false) {
            throw new LocalizedException(__('The fixture %1 does not exist in fixtures list', $fixtureToFind));
        }

        return $offset;
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
            [$this->getFixtureAsArray($fixture)],
            array_slice($fixtures, $position)
        );
    }

    /**
     * Creates an array with the supplied fixture factory
     *
     * @param string $fixture
     * @return string[]
     */
    private function getFixtureAsArray(string $fixture): array
    {
        return [
            'factory' => $fixture
        ];
    }
}
