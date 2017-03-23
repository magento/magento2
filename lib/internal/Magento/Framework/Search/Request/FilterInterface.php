<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

/**
 * Filter Interface
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
     */
    public function getType();

    /**
     * Get Name
     *
     * @return string
     */
    public function getName();
}
