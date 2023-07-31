<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

/**
 * Collects shared objects from ObjectManager and clones properties for later comparison
 */
class CollectedObject
{
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

    private static ?CollectedObject $skippedObject = null;
    public static function getSkippedObject() {
        if (!self::$skippedObject) {
            self::$skippedObject = new CollectedObject('(skipped)', [], 0);
        }
        return self::$skippedObject;
    }
    private static ?CollectedObject $recursionEndObject = null;
    public static function getRecursionEndObject() {
        if (!self::$recursionEndObject) {
            self::$recursionEndObject = new CollectedObject('(end of recursion level)', [], 0);
        }
        return self::$recursionEndObject;
    }
}
