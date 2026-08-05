<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\Block\Adminhtml\System\Config;

/**
 * Elasticsearch 8.x test connection block
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class TestConnection extends \Magento\AdvancedSearch\Block\Adminhtml\System\Config\TestConnection
{
    /**
     * @inheritdoc
     */
    public function _getFieldMapping(): array
    {
        $fields = [
            'engine' => 'catalog_search_engine',
            'hostname' => 'catalog_search_elasticsearch8_server_hostname',
            'port' => 'catalog_search_elasticsearch8_server_port',
            'index' => 'catalog_search_elasticsearch8_index_prefix',
            'enableAuth' => 'catalog_search_elasticsearch8_enable_auth',
            'username' => 'catalog_search_elasticsearch8_username',
            'password' => 'catalog_search_elasticsearch8_password',
            'timeout' => 'catalog_search_elasticsearch8_server_timeout',
        ];

        return array_merge(parent::_getFieldMapping(), $fields);
    }
}
