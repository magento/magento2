<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;

class RateQuery
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    private $request;

    /**
     * RateQuery constructor.
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    public function prepareSelect(\Magento\Framework\DB\Select $select)
    {
        $select->where(
            'website_id = :website_id'
        )->order(
            ['dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'condition_value DESC']
        )->limit(
            1
        );

        // Render destination condition
        $orWhere = '(' . implode(
            ') OR (',
            [
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",

                // Handle asterisk in dest_zip field
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = '*'",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = '*'",
                "dest_country_id = '0' AND dest_region_id = :region_id AND dest_zip = '*'",
                "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = '*'",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = '*'"
            ]
        ) . ')';
        $select->where($orWhere);

        // Render condition by condition name
        if (is_array($this->request->getConditionName())) {
            $orWhere = [];
            foreach (range(0, count($this->request->getConditionName())) as $conditionNumber) {
                $bindNameKey = sprintf(':condition_name_%d', $conditionNumber);
                $bindValueKey = sprintf(':condition_value_%d', $conditionNumber);
                $orWhere[] = "(condition_name = {$bindNameKey} AND condition_value <= {$bindValueKey})";
            }

            if ($orWhere) {
                $select->where(implode(' OR ', $orWhere));
            }
        } else {
            $select->where('condition_name = :condition_name');
            $select->where('condition_value <= :condition_value');
        }
        return $select;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        $bind = [
            ':website_id' => (int)$this->request->getWebsiteId(),
            ':country_id' => $this->request->getDestCountryId(),
            ':region_id' => (int)$this->request->getDestRegionId(),
            ':postcode' => $this->request->getDestPostcode(),
        ];

        // Render condition by condition name
        if (is_array($this->request->getConditionName())) {
            $i = 0;
            foreach ($this->request->getConditionName() as $conditionName) {
                $bindNameKey = sprintf(':condition_name_%d', $i);
                $bindValueKey = sprintf(':condition_value_%d', $i);
                $bind[$bindNameKey] = $conditionName;
                $bind[$bindValueKey] = $this->request->getData($conditionName);
                $i++;
            }
        } else {
            $bind[':condition_name'] = $this->request->getConditionName();
            $bind[':condition_value'] = round($this->request->getData($this->request->getConditionName()), 4);
        }

        return $bind;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
}
