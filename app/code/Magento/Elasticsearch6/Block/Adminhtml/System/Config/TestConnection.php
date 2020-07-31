<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch6\Block\Adminhtml\System\Config;

/**
 * Elasticsearch 6.x test connection block
 *
 * @deprecated in favor of Elasticsearch 7.
 */
class TestConnection extends \Magento\AdvancedSearch\Block\Adminhtml\System\Config\TestConnection
{
    /**
     * @inheritdoc
     */
    protected function _getFieldMapping()
    {
        $fields = [
            'engine' => 'catalog_search_engine',
            'hostname' => 'catalog_search_elasticsearch6_server_hostname',
            'port' => 'catalog_search_elasticsearch6_server_port',
            'index' => 'catalog_search_elasticsearch6_index_prefix',
            'enableAuth' => 'catalog_search_elasticsearch6_enable_auth',
            'username' => 'catalog_search_elasticsearch6_username',
            'password' => 'catalog_search_elasticsearch6_password',
            'timeout' => 'catalog_search_elasticsearch6_server_timeout',
        ];

        return array_merge(parent::_getFieldMapping(), $fields);
    }
}
