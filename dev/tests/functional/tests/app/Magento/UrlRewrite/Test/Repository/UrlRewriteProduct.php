<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\UrlRewrite\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class UrlRewriteProduct
 * URL Rewrite Product Repository
 *
 */
class UrlRewriteProduct extends AbstractRepository
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
                'url_rewrite_type' => 'For product',
                'fields' => [
                    'request_path' => [
                        'value' => '%rewritten_product_request_path%',
                    ],
                    'store_id' => [
                        'value' => 'Main Website/Main Website Store/Default Store View',
                    ],
                ],
            ],
        ];
        $this->_data['product_with_temporary_redirect'] = $this->_data['default'];
        $this->_data['product_with_temporary_redirect']['data']['fields']['redirect_type'] = [
            'value' => 'Temporary (302)',
        ];
    }
}
