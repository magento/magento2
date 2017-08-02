<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model;

/**
 * Flag indicates that some rules have changed but changes have not been applied yet.
 * @since 2.0.0
 */
class Flag extends \Magento\Framework\Flag
{
    /**
     * Flag code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_flagCode = 'catalog_rules_dirty';
}
