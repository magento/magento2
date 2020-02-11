<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldBackendUi\Model;

use Magento\Framework\Model\AbstractModel;
use Mod\HelloWorldBackendUi\Model\ResourceModel\Grid as ResourceModelGrid;

/**
 * Extra comments grid model.
 */
class Grid extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModelGrid::class);
    }
}
