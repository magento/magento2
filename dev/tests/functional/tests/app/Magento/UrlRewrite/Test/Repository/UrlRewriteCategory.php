<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class UrlRewriteCategory
 * URL Rewrite Category Repository
 *
 */
class UrlRewriteCategory extends AbstractRepository
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
            'config' => $defaultConfig,
            'data' => [
                'url_rewrite_type' => 'For category',
                'fields' => [
                    'request_path' => [
                        'value' => '%rewritten_category_request_path%',
                    ],
                    'store_id' => [
                        'value' => 'Main Website/Main Website Store/Default Store View',
                    ],
                ],
            ],
        ];
        $this->_data['category_with_permanent_redirect'] = $this->_data['default'];
        $this->_data['category_with_permanent_redirect']['data']['fields']['redirect_type'] = [
            'value' => 'Permanent (301)',
        ];
    }
}
