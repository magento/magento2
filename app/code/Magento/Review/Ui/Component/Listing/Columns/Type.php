<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Type
 */
class Type extends Column
{
    /**
     * {@inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item['type'] = $this->getTypeLabel($item);
        }

        return $dataSource;
    }

    /**
     * Retrieve type label
     *
     * @param array $item
     * @return \Magento\Framework\Phrase
     */
    protected function getTypeLabel(array $item)
    {
        if (!empty($item['customer_id'])) {
            return __('Customer');
        }

        if (isset($item['store_id']) && $item['store_id'] == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            return __('Administrator');
        }

        return __('Guest');
    }
}
