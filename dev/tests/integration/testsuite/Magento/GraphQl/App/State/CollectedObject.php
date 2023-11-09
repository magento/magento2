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
    /**
     * @var CollectedObject|null
     */
    private static ?CollectedObject $skippedObject = null;

    /**
     * @var CollectedObject|null
     */
    private static ?CollectedObject $recursionEndObject = null;

    /**
     * @param string $className
     * @param array $properties
     * @param int $objectId
     */
    public function __construct(
        private readonly string $className,
        private readonly array $properties,
        private readonly int $objectId,
    ) {
    }

    /**
     * Returns the class name of the object
     *
     * @return string
     */
    public function getClassName() : string
    {
        return $this->className;
    }

    /**
     * Returns the properties of the object
     *
     * @return array
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    /**
     * Returns the object id
     *
     * @return int
     */
    public function getObjectId() : int
    {
        return $this->objectId;
    }

    /**
     * Returns a special object that is used to mark a skipped object.
     *
     * @return CollectedObject
     */
    public static function getSkippedObject() : CollectedObject
    {
        if (!self::$skippedObject) {
            self::$skippedObject = new CollectedObject('(skipped)', [], 0);
        }
        return self::$skippedObject;
    }
    /**
     * Returns a special object that is used to mark the end of a recursion level.
     *
     * @return CollectedObject
     */

    public static function getRecursionEndObject() : CollectedObject
    {
        if (!self::$recursionEndObject) {
            self::$recursionEndObject = new CollectedObject('(end of recursion level)', [], 0);
        }
        return self::$recursionEndObject;
    }
}
