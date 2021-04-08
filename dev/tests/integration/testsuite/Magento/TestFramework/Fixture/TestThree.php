<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\DataFixtureTestStorage;

/**
 * Test data fixture
 */
class TestThree implements DataFixtureInterface
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
    public function apply(DataObject $data): ?DataObject
    {
        $fixtures = $this->storage->getData('fixtures') ?? [];
        $fixtures[$data->getData('key')] = $data->getData('value');
        $this->storage->setData('fixtures', $fixtures);
        return null;
    }
}
