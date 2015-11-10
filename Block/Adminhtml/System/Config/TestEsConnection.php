<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\AdvancedSearch\Block\Adminhtml\System\Config\TestConnection;

/**
 * Elasticsearch test connection block
 */
class TestEsConnection extends TestConnection
{
    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('elasticsearch/search_system_config_testconnection/ping'),
                'field_mapping' => [
                    'hostname' => 'catalog_search_elasticsearch_server_hostname',
                    'port' => 'catalog_search_elasticsearch_server_port',
                    'index' => 'catalog_search_elasticsearch_index_name',
                    'enable_auth' => 'catalog_search_elasticsearch_enable_auth',
                    'username' => 'catalog_search_elasticsearch_username',
                    'password' => 'catalog_search_elasticsearch_password',
                    'timeout' => 'catalog_search_elasticsearch_server_timeout',
                ],
            ]
        );

        return $this->_toHtml();
    }
}
