<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

/**
 * @api
 */
interface TypeListInterface
{
    /**
     * Get information about all declared cache types
     *
     * @return array
     */
    public function getTypes();

    /**
     * Get label information of available cache types
     *
     * @return array
     */
    public function getTypeLabels();

    /**
     * Get array of all invalidated cache types
     *
     * @return array
     */
    public function getInvalidated();

    /**
     * Mark specific cache type(s) as invalidated
     *
     * @param string|array $typeCode
     * @return void
     */
    public function invalidate($typeCode);

    /**
     * Clean cached data for specific cache type
     *
     * @param string $typeCode
     * @return void
     */
    public function cleanType($typeCode);
}
