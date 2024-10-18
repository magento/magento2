<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Data;

use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Replaces uniqid placeholder in the provided data with unique ID
 */
class UniqueIdProcessor implements ProcessorInterface
{
    private const PLACEHOLDER = '%uniqid%';

    /**
     * @var int
     */
    private const INCREMENT = 1;

    /**
     * @var array
     */
    private static $storage = [];

    /**
     * @inheritdoc
     */
    public function process(DataFixtureInterface $fixture, array $data): array
    {
        $class = get_class($fixture);
        if (!isset(self::$storage[$class])) {
            self::$storage[$class] = ['prefix' => uniqid(), 'increment' => self::INCREMENT];
        }
        $hash = self::$storage[$class]['prefix'] . self::$storage[$class]['increment']++;
        array_walk_recursive($data, function (&$value) use ($hash) {
            if (is_string($value)) {
                $value = str_replace(self::PLACEHOLDER, $hash, $value);
            }
        });
        return $data;
    }
}
