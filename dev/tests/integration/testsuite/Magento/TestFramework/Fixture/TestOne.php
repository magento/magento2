<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\TestFramework\DataFixtureTestStorage;

/**
 * Test data fixture
 */
class TestOne implements RevertibleDataFixtureInterface
{
    /**
     * @var DataFixtureTestStorage
     */
    private $storage;

    /**
     * @param DataFixtureTestStorage $storage
     */
    public function __construct(DataFixtureTestStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $fixtures = $this->storage->getData('fixtures') ?? [];
        $result = array_merge([get_class($this) => true], $data);
        $this->storage->setData('fixtures', array_merge($fixtures, $result));
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        $fixtures = $this->storage->getData('fixtures') ?? [];
        foreach (array_keys($data) as $key) {
            unset($fixtures[$key]);
        }
        $this->storage->setData('fixtures', $fixtures);
    }
}
