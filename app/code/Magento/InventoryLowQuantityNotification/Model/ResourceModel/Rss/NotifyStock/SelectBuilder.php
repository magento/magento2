<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Framework\DB\Select;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\SelectBuilder\ApplyConfigurationCondition;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\SelectBuilder\ApplyNameAttributeCondition;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\SelectBuilder\ApplyStatusAttributeCondition;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\SelectBuilder\BaseSelectProvider;

class SelectBuilder
{
    /**
     * @var BaseSelectProvider
     */
    private $baseSelectProvider;

    /**
     * @var ApplyStatusAttributeCondition
     */
    private $applyStatusAttributeCondition;

    /**
     * @var ApplyNameAttributeCondition
     */
    private $applyNameAttributeCondition;

    /**
     * @var ApplyConfigurationCondition
     */
    private $applyConfigurationCondition;

    /**
     * SelectBuilder constructor.
     *
     * @param BaseSelectProvider $baseSelectProvider
     * @param ApplyStatusAttributeCondition $applyStatusAttributeCondition
     * @param ApplyNameAttributeCondition $applyNameAttributeCondition
     * @param ApplyConfigurationCondition $applyConfigurationCondition
     */
    public function __construct(
        BaseSelectProvider $baseSelectProvider,
        ApplyStatusAttributeCondition $applyStatusAttributeCondition,
        ApplyNameAttributeCondition $applyNameAttributeCondition,
        ApplyConfigurationCondition $applyConfigurationCondition
    ) {
        $this->baseSelectProvider = $baseSelectProvider;
        $this->applyStatusAttributeCondition = $applyStatusAttributeCondition;
        $this->applyNameAttributeCondition = $applyNameAttributeCondition;
        $this->applyConfigurationCondition = $applyConfigurationCondition;
    }

    /**
     * @param Select $select
     *
     * @return void
     */
    public function build(Select $select)
    {
        $this->baseSelectProvider->build($select);

        $this->applyConfigurationCondition->execute($select);
        $this->applyNameAttributeCondition->execute($select);
        $this->applyStatusAttributeCondition->execute($select);
    }
}
