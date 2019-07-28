<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Handler\Widget;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating widgetInstance/frontendApp.
 */
class Curl extends AbstractCurl
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'code' => [
            'CMS Page Link' => 'cms_page_link',
            'Recently Viewed Products' => 'recently_viewed',
            'Catalog New Products List' => 'new_products',
        ],
        'block' => [
            'Main Content Area' => 'content',
            'Sidebar Additional' => 'sidebar.additional',
            'Sidebar Main' => 'sidebar.main',
        ],
        'page_group' => [
            'Generic Pages/All Pages' => 'all_pages',
            'Generic Pages/Specified Page' => 'pages',
            'Generic Pages/Page Layouts' => 'page_layouts',
            'Categories/Non-Anchor Categories' => 'notanchor_categories',
        ],
        'template' => [
            'CMS Page Link Block Template' => 'widget/link/link_block.phtml',
            'New Products List Template' => 'product/widget/new/content/new_grid.phtml',
        ],
        'layout_handle' => [
            'Shopping Cart' => 'checkout_cart_index',
        ],
        'display_type' => [
            'All products' => 'all_products',
            'New products' => 'new_products',
        ],
        'show_pager' => [
            'No' => '0',
            'Yes' => '1',
        ],
    ];

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $additionalMappingData = [];

    /**
     * Widget Instance Template.
     *
     * @var string
     */
    protected $widgetInstanceTemplate = '';

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        $this->mappingData = array_merge($this->mappingData, $this->additionalMappingData);
        parent::__construct($configuration, $eventManager);
    }

    /**
     * Post request for creating widget instance.
     *
     * @param FixtureInterface $fixture [optional]
     * @throws \Exception
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $code = $this->mappingData['code'][$fixture->getCode()];
        $themeId = $this->getThemeId($fixture->getThemeId());
        $data = $this->prepareData($fixture);

        $url = $_ENV['app_backend_url'] . 'admin/widget_instance/save/code/' . $code . '/theme_id/' . $themeId;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Widget instance creation by curl handler was not successful! Response: $response");
        }
        $id = null;
        if (preg_match_all('/\/widget_instance\/edit\/instance_id\/(\d+)/', $response, $matches)) {
            $id = $matches[1][count($matches[1]) - 1];
        }
        return ['id' => $id];
    }

    /**
     * Prepare data for create widget.
     *
     * @param FixtureInterface $widget
     * @return array
     */
    protected function prepareData(FixtureInterface $widget)
    {
        $data = $this->replaceMappingData($widget->getData());
        if ($widget->hasData('store_ids')) {
            $data['store_ids'][0] = $widget->getDataFieldConfig('store_ids')['source']->getStores()[0]->getStoreId();
        }
        unset($data['code']);
        unset($data['theme_id']);

        $data = $this->prepareWidgetInstance($data);
        $data = $this->prepareParameters($data);

        return $data;
    }

    /**
     * Prepare widget Frontend options.
     *
     * @param array $data
     * @return array
     */
    protected function prepareParameters(array $data)
    {
        return $this->prepareEntity($data);
    }

    /**
     * Prepare entity parameters data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareEntity(array $data)
    {
        if (isset($data['parameters']['entities'])) {
            $data['parameters']['page_id'] = $data['parameters']['entities'][0]->getPageId();
            unset($data['parameters']['entities']);
        }

        return $data;
    }

    /**
     * Prepare Widget Instance (layout) data.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function prepareWidgetInstance(array $data)
    {
        $widgetInstances = [];
        foreach ($data['widget_instance'] as $key => $widgetInstance) {
            $pageGroup = $widgetInstance['page_group'];
            $method = 'prepare' . str_replace('_', '', ucwords($pageGroup, '_')) . 'Group';
            if (!method_exists(__CLASS__, $method)) {
                throw new \Exception('Method for prepare page group "' . $method . '" is not exist.');
            }
            $widgetInstances[$key]['page_group'] = $pageGroup;
            $widgetInstances[$key][$pageGroup] = $this->$method($widgetInstance);
            if (!isset($widgetInstance[$pageGroup]['page_id'])) {
                $widgetInstances[$key][$pageGroup]['page_id'] = 0;
            }
        }
        $data['widget_instance'] = $widgetInstances;

        return $data;
    }

    /**
     * Prepare All Page Group.
     *
     * @param array $widgetInstancePageGroup
     * @return array
     */
    protected function prepareAllPagesGroup(array $widgetInstancePageGroup)
    {
        $widgetInstance['layout_handle'] = isset($widgetInstancePageGroup['layout_handle'])
            ? $widgetInstancePageGroup['layout_handle']
            : 'default';
        $widgetInstance['for'] = 'all';
        $widgetInstance['block'] = $widgetInstancePageGroup['block'];
        $widgetInstance['template'] = isset($widgetInstancePageGroup['template'])
            ? $widgetInstancePageGroup['template']
            : $this->widgetInstanceTemplate;

        return $widgetInstance;
    }

    /**
     * Prepare Non-Anchor Categories Page Group.
     *
     * @param array $widgetInstancePageGroup
     * @return array
     */
    protected function prepareNotanchorCategoriesGroup(array $widgetInstancePageGroup)
    {
        $widgetInstancePageGroup['is_anchor_only'] = 0;
        $widgetInstancePageGroup['for'] = 'all';
        $widgetInstancePageGroup['layout_handle'] = 'catalog_category_view_type_default';

        return $widgetInstancePageGroup;
    }

    /**
     * Prepare Specified Page Group.
     *
     * @param array $widgetInstancePageGroup
     * @return array
     */
    protected function preparePagesGroup(array $widgetInstancePageGroup)
    {
        $widgetInstancePageGroup['for'] = 'all';

        return $widgetInstancePageGroup;
    }

    /**
     * Return theme id by title.
     *
     * @param string $title
     * @return int
     * @throws \Exception
     */
    protected function getThemeId($title)
    {
        $url = $_ENV['app_backend_url'] . 'mui/index/render/';
        $data = [
            'namespace' => 'design_theme_listing',
            'filters' => [
                'placeholder' => true,
                'theme_title' => $title
            ],
            'isAjax' => true
        ];
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);

        $curl->write($url, $data, CurlInterface::POST);
        $response = $curl->read();
        $curl->close();

        preg_match('/design_theme_listing_data_source.+items.+"theme_id":"(\d+)"/', $response, $match);
        return empty($match[1]) ? null : $match[1];
    }
}
