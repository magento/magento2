<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        ],
        'block' => [
            'Main Content Area' => 'content',
            'Sidebar Additional' => 'sidebar.additional',
            'Sidebar Main' => 'sidebar.main',
        ],
        'page_group' => [
            'All Pages' => 'all_pages',
            'Specified Page' => 'pages',
            'Page Layouts' => 'page_layouts',
            'Non-Anchor Categories' => 'notanchor_categories',
        ],
        'template' => [
            'CMS Page Link Block Template' => 'widget/link/link_block.phtml',
        ],
        'layout_handle' => [
            'Shopping Cart' => 'checkout_cart_index',
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
     * Constructor
     *
     * @constructor
     * @param DataInterface $configuration
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
     * @return null|array instance id
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . 'admin/widget_instance/save/code/'
            . $data['code'] . '/theme_id/' . $data['theme_id'];
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);

        if (isset($data['page_id'])) {
            $data['parameters']['page_id'] = $data['page_id'][0];
            unset($data['page_id']);
        }
        $data['parameters']['unique_id'] = md5(microtime(1));
        if ($fixture->hasData('store_ids')) {
            $data['store_ids'][0] = $fixture->getDataFieldConfig('store_ids')['source']->getStores()[0]->getStoreId();
        }
        unset($data['code']);
        unset($data['theme_id']);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
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
        $data['theme_id'] = $this->getThemeId($data['theme_id']);

        return $this->prepareWidgetInstance($data);
    }

    /**
     * Prepare Widget Instance data.
     *
     * @param array $data
     * @throws \Exception
     * @return array
     */
    protected function prepareWidgetInstance($data)
    {
        foreach ($data['widget_instance'] as $key => $widgetInstance) {
            $pageGroup = $widgetInstance['page_group'];

            if (!isset($widgetInstance[$pageGroup]['page_id'])) {
                $widgetInstance[$pageGroup]['page_id'] = 0;
            }
            $method = 'prepare' . str_replace(' ', '', ucwords(str_replace('_', ' ', $pageGroup))) . 'Group';
            if (!method_exists(__CLASS__, $method)) {
                throw new \Exception('Method for prepare page group "' . $method . '" is not exist.');
            }
            $widgetInstance[$pageGroup] = $this->$method($widgetInstance[$pageGroup]);
            $data['widget_instance'][$key] = $widgetInstance;
        }

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
        $widgetInstancePageGroup['layout_handle'] = 'default';
        $widgetInstancePageGroup['for'] = 'all';
        if (!isset($widgetInstancePageGroup['template'])) {
            $widgetInstancePageGroup['template'] = $this->widgetInstanceTemplate;
        }

        return $widgetInstancePageGroup;
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
        $filter = $this->encodeFilter(['theme_title' => $title]);
        $url = $_ENV['app_backend_url'] . 'admin/system_design_theme/grid/filter/' . $filter;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();

        preg_match('/<tr data-role="row" title="[^"]+system_design_theme\/edit\/id\/([\d]+)\/"/', $response, $match);
        return empty($match[1]) ? null : $match[1];
    }
}
