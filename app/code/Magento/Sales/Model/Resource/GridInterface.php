<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Resource;

/**
 * Interface GridInterface
 */
interface GridInterface
{
    /**
     * @param int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refresh($value, $field = null);

    /**
     * @param int|string $value
     * @param null|string $field
     * @return int
     */
    public function purge($value, $field = null);
}
