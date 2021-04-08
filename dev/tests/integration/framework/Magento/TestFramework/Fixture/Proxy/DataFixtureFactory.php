<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Proxy;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixturePathResolver;

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
     * @var DataFixturePathResolver
     */
    private $fixturePathResolver;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DataFixturePathResolver $fixturePathResolver
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DataFixturePathResolver $fixturePathResolver
    ) {
        $this->objectManager = $objectManager;
        $this->fixturePathResolver = $fixturePathResolver;
    }

    /**
     * Create new instance of data fixture
     *
     * @param array $directives
     * @return DataFixtureInterface
     */
    public function create(array $directives): DataFixtureInterface
    {
        if (is_callable($directives['name'])) {
            $result = $this->objectManager->create(
                CallableDataFixture::class,
                [
                    'callback' => $directives['name']
                ]
            );
        } elseif (class_exists($directives['name'])) {
            $result = $this->objectManager->create(
                DataFixture::class,
                [
                    'className' => $directives['name'],
                ]
            );
        } else {
            $result = $this->objectManager->create(
                LegacyDataFixture::class,
                [
                    'filePath' => $this->fixturePathResolver->resolve($directives['name']),
                ]
            );
        }

        return $result;
    }
}
