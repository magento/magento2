<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
