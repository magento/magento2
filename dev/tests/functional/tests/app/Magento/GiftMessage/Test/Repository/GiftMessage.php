<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class GiftMessage
 * GiftMessage repository
 */
class GiftMessage extends AbstractRepository
{
    /**
     * @construct
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'sender' => 'John Doe',
            'recipient' => 'Jane Doe',
            'message' => 'text_%isolation%',
        ];
    }
}
