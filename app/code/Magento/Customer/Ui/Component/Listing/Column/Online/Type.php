<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column\Online;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Model\Visitor;

/**
 * Class Type
 * @since 2.0.0
 */
class Type extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     * @since 2.0.0
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $item[$this->getData('name')] == Visitor::VISITOR_TYPE_VISITOR
                    ? __('Visitor')
                    : __('Customer');
            }
        }

        return $dataSource;
    }
}
