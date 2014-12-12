<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
