<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Plugin;

use Magento\Store\Model\Store;

class StoreAddress
{
    /**
     * Make formatted store address accessible for templates
     *
     * @param Store $subject
     * @param string $key
     * @param string|int $index
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetData(Store $subject, $key = '', $index = null)
    {
        if ($key === 'formatted_address') {
            $subject->setData('formatted_address', $subject->getFormattedAddress());
        }

        return null;
    }
}
