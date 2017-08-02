<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

/**
 * Filter Interface
 *
 * @api
 * @since 2.0.0
 */
interface FilterInterface
{
    /**
     * #@+ Filter Types
     */
    const TYPE_TERM = 'termFilter';

    const TYPE_BOOL = 'boolFilter';

    const TYPE_RANGE = 'rangeFilter';

    const TYPE_WILDCARD = 'wildcardFilter';

    /**#@-*/

    /**
     * Get Type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Get Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();
}
