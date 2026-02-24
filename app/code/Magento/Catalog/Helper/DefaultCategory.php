<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Helper;

/**
 * Default Category helper
 */
class DefaultCategory
{
    /**
     * Default Category ID
     *
     * @var int
     */
    private $defaultCategoryId = 2;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->defaultCategoryId;
    }
}
