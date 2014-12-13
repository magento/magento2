<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Newsletter\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class UrlRewrite
 * Data for creation url rewrite
 */
class Template extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'code' => 'Newsletter Template %isolation%',
            'subject' => 'Newsletter Subject %isolation%',
            'sender_name' => 'Sender Name %isolation%',
            'sender_email' => 'support%isolation%@example.com',
            'text' => 'Template Content %isolation%',
        ];
    }
}
