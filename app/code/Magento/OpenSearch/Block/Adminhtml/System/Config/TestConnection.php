<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Block\Adminhtml\System\Config;

/**
 * OpenSearch test connection block
 */
class TestConnection extends \Magento\AdvancedSearch\Block\Adminhtml\System\Config\TestConnection
{
    /**
     * @inheritdoc
     */
    protected function _getFieldMapping(): array
    {
        $fields = [
            'hostname' => 'catalog_search_opensearch_server_hostname',
            'port' => 'catalog_search_opensearch_server_port',
            'index' => 'catalog_search_opensearch_index_prefix',
            'enableAuth' => 'catalog_search_opensearch_enable_auth',
            'username' => 'catalog_search_opensearch_username',
            'password' => 'catalog_search_opensearch_password',
            'timeout' => 'catalog_search_opensearch_server_timeout',
        ];

        return array_merge(parent::_getFieldMapping(), $fields);
    }
}
