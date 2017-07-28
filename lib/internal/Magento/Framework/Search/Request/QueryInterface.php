<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

/**
 * Query Interface
 *
 * @api
 * @since 2.0.0
 */
interface QueryInterface
{
    /**
     * #@+ Query Types
     */
    const TYPE_MATCH = 'matchQuery';

    const TYPE_BOOL = 'boolQuery';

    const TYPE_FILTER = 'filteredQuery';

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

    /**
     * Get Boost
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getBoost();
}
