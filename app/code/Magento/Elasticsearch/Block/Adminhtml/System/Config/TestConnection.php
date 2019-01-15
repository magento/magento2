<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Block\Adminhtml\System\Config;

/**
 * Elasticsearch test connection block
 * @codeCoverageIgnore
 */
class TestConnection extends \Magento\AdvancedSearch\Block\Adminhtml\System\Config\TestConnection
{
    /**
     * {@inheritdoc}
     */
    protected function _getFieldMapping()
    {
        $fields = [
            'engine' => 'catalog_search_engine',
            'hostname' => 'catalog_search_elasticsearch_server_hostname',
            'port' => 'catalog_search_elasticsearch_server_port',
            'index' => 'catalog_search_elasticsearch_index_prefix',
            'enableAuth' => 'catalog_search_elasticsearch_enable_auth',
            'username' => 'catalog_search_elasticsearch_username',
            'password' => 'catalog_search_elasticsearch_password',
            'timeout' => 'catalog_search_elasticsearch_server_timeout',
        ];
        return array_merge(parent::_getFieldMapping(), $fields);
    }
}
