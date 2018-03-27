<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Framework\DB\Select;

class SelectBuilder
{
    /**
     * @var ApplyBaseJoins
     */
    private $applyBaseJoins;

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
     * @param ApplyBaseJoins $applyBaseJoins
     * @param ApplyStatusAttributeCondition $applyStatusAttributeCondition
     * @param ApplyNameAttributeCondition $applyNameAttributeCondition
     * @param ApplyConfigurationCondition $applyConfigurationCondition
     */
    public function __construct(
        ApplyBaseJoins $applyBaseJoins,
        ApplyStatusAttributeCondition $applyStatusAttributeCondition,
        ApplyNameAttributeCondition $applyNameAttributeCondition,
        ApplyConfigurationCondition $applyConfigurationCondition
    ) {
        $this->applyBaseJoins = $applyBaseJoins;
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
        $this->applyBaseJoins->execute($select);
        $this->applyConfigurationCondition->execute($select);
        $this->applyNameAttributeCondition->execute($select);
        $this->applyStatusAttributeCondition->execute($select);
    }
}
