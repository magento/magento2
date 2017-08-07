<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper;

/**
 * Default Category helper
 * @since 2.1.0
 */
class DefaultCategory
{
    /**
     * Default Category ID
     *
     * @var int
     * @since 2.1.0
     */
    private $defaultCategoryId = 2;

    /**
     * @return int
     * @since 2.1.0
     */
    public function getId()
    {
        return $this->defaultCategoryId;
    }
}
