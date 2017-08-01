<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Reader;

/**
 * Interface \Magento\Framework\Code\Reader\ClassReaderInterface
 *
 * @since 2.0.0
 */
interface ClassReaderInterface
{
    /**
     * Read class constructor signature
     *
     * @param string $className
     * @return array|null
     * @throws \ReflectionException
     * @since 2.0.0
     */
    public function getConstructor($className);

    /**
     * Retrieve parent relation information for type in a following format
     * array(
     *     'Parent_Class_Name',
     *     'Interface_1',
     *     'Interface_2',
     *     ...
     * )
     *
     * @param string $className
     * @return string[]
     * @since 2.0.0
     */
    public function getParents($className);
}
