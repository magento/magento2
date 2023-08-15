<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

/**
 * Immutable recursive data structure that holds copy of properties from collected objects.  Created by Collector.
 */
class CollectedObject
{
    private static ?CollectedObject $skippedObject = null;
    private static ?CollectedObject $recursionEndObject = null;

    public function __construct(
        private readonly string $className,
        private readonly array $properties,
        private readonly int $objectId,
    ) {
    }

    public function getClassName() : string
    {
        return $this->className;
    }

    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getObjectId() : int
    {
        return $this->objectId;
    }

    public static function getSkippedObject()
    {
        if (!self::$skippedObject) {
            self::$skippedObject = new CollectedObject('(skipped)', [], 0);
        }
        return self::$skippedObject;
    }

    public static function getRecursionEndObject()
    {
        if (!self::$recursionEndObject) {
            self::$recursionEndObject = new CollectedObject('(end of recursion level)', [], 0);
        }
        return self::$recursionEndObject;
    }
}
