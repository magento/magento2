<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldBackendUi\Model\ResourceModel\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mod\HelloWorldBackendUi\Model\Grid;
use Mod\HelloWorldBackendUi\Model\ResourceModel\Grid as GridResource;

/**
 * Extra comments grid collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Grid::class, GridResource::class);
    }
}
