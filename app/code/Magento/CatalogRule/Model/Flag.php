<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model;

/**
 * Flag indicates that some rules have changed but changes have not been applied yet.
 */
class Flag extends \Magento\Framework\Flag
{
    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'catalog_rules_dirty';
}
