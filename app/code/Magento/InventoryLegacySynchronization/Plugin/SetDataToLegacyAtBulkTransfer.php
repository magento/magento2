<?php
/**
 * Created by PhpStorm.
 * User: riccardo
 * Date: 22/03/19
 * Time: 14.35
 */
namespace Magento\InventoryLegacySynchronization;

class SetDataToLegacyAtBulkTransfer
{


    public function afterExecute(\Magento\InventoryCatalog\Model\ResourceModel\BulkInventoryTransfer $subject, $result)
    {
    }
}