<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Type;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureTypeInterface;

/**
 * Factory for data fixture type
 */
class Factory
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
     * @param array $directives
     * @return DataFixtureTypeInterface
     */
    public function create(array $directives): DataFixtureTypeInterface
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
                    'filePath' => $directives['name'],
                ]
            );
        }

        return $result;
    }
}
