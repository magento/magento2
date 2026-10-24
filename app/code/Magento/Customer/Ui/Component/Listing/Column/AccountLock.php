<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class AccountLock extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (array_key_exists('lock_expires', $item)) {
                    $lockExpires = new \DateTime($item['lock_expires'] ?? 'now');
                    if ($lockExpires > new \DateTime()) {
                        $item['lock_expires'] =  __('Locked');
                    } else {
                        $item['lock_expires'] = __('Unlocked');
                    }
                } else {
                    $item['lock_expires'] = __('Unlocked');
                }
            }
        }
        return $dataSource;
    }
}
