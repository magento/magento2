<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

/**
 * Apply and revert data fixtures
 */
class DataFixtureSetup
{
    /**
     * @var DataFixtureFactory
     */
    private $dataFixtureFactory;

    /**
     * @param DataFixtureFactory $dataFixtureFactory
     */
    public function __construct(
        DataFixtureFactory $dataFixtureFactory
    ) {
        $this->dataFixtureFactory = $dataFixtureFactory;
    }

    /**
     * Applies data fixture and returns the result
     *
     * @param string $factory
     * @param array $data
     * @return array|null
     */
    public function apply(string $factory, array $data = []): ?array
    {
        $fixture = $this->dataFixtureFactory->create($factory);
        return $fixture->apply($data);
    }

    /**
     * Revert data fixture
     *
     * @param string $factory
     * @param array $data
     */
    public function revert(string $factory, array $data): void
    {
        $fixture = $this->dataFixtureFactory->create($factory);
        if ($fixture instanceof RevertibleDataFixtureInterface) {
            $fixture->revert($data);
        }
    }
}
