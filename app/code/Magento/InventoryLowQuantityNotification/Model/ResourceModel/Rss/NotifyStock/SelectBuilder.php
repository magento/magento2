<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class SelectBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ApplyNameAttributeJoin
     */
    private $applyNameAttributeJoin;

    /**
     * @var ApplyStatusAttributeJoin
     */
    private $applyStatusAttributeJoin;

    /**
     * @var ApplyConfigurationCondition
     */
    private $applyConfigurationCondition;

    /**
     * @var ApplyBaseJoins
     */
    private $applyBaseJoins;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ApplyBaseJoins $applyBaseJoins
     * @param ApplyNameAttributeJoin $applyNameAttributeJoin
     * @param ApplyStatusAttributeJoin $applyStatusAttributeJoin
     * @param ApplyConfigurationCondition $applyConfigurationCondition
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ApplyBaseJoins $applyBaseJoins,
        ApplyNameAttributeJoin $applyNameAttributeJoin,
        ApplyStatusAttributeJoin $applyStatusAttributeJoin,
        ApplyConfigurationCondition $applyConfigurationCondition
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->applyBaseJoins = $applyBaseJoins;
        $this->applyNameAttributeJoin = $applyNameAttributeJoin;
        $this->applyStatusAttributeJoin = $applyStatusAttributeJoin;
        $this->applyConfigurationCondition = $applyConfigurationCondition;
    }

    /**
     * @param Select $select
     *
     * @return void
     */
    public function build(Select $select)
    {
        $this->applyBaseJoins->execute($select);
        $this->applyNameAttributeJoin->execute($select);
        $this->applyStatusAttributeJoin->execute($select);
        $this->applyConfigurationCondition->execute($select);
    }
}
