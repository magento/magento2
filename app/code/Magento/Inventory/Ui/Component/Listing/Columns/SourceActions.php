<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Ui\Component\Listing\Columns;

use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class SourceActions
 */
class SourceActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * Add new edit action for the ui grid.
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";

                if (isset($item[SourceInterface::SOURCE_ID])) {
                    $id = $item[SourceInterface::SOURCE_ID];
                }

                $href = $this->getContext()->getUrl(
                    "inventory/sources/edit",
                    [SourceInterface::SOURCE_ID => $id]
                );

                $item[$name]["view"] = [
                    "href" => $href,
                    "label" => __("Edit")
                ];
            }
        }
        return $dataSource;
    }
}
