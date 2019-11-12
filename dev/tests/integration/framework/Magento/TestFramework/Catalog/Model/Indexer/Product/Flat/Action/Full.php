<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class Full reindex action
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full
{
    /**
     * List of product types available in installation
     *
     * @var array
     */
    protected $_productTypes;
}
