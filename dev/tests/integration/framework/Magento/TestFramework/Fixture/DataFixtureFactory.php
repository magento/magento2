<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for data fixture
 */
class DataFixtureFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new instance of data fixture
     *
     * @param string $fixture
     * @return DataFixtureInterface
     */
    public function create(string $fixture): DataFixtureInterface
    {
        if (is_callable($fixture)) {
            $result = $this->objectManager->create(
                CallableDataFixture::class,
                [
                    'callback' => $fixture
                ]
            );
        } elseif (class_exists($fixture)) {
            $result = $this->objectManager->create($fixture);
        } else {
            $result = $this->objectManager->create(
                LegacyDataFixture::class,
                [
                    'filePath' => $fixture,
                ]
            );
        }

        return $result;
    }
}
