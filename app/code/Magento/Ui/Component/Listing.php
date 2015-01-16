<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\Component\Control\ActionPool;
use Magento\Ui\Component\Listing\OptionsFactory;
use Magento\Ui\Component\Listing\RowPool;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Ui\DataProvider\Factory as DataProviderFactory;
use Magento\Ui\DataProvider\Manager;

/**
 * Class Listing
 */
class Listing extends AbstractView
{
    /**
     * Options provider key in data array
     */
    const OPTIONS_PROVIDER_KEY = 'options_provider';

    /**
     * Row data provider key in data array
     */
    const ROW_DATA_PROVIDER_KEY = 'row_data_provider';

    /**
     * Data provider row pool
     *
     * @var RowPool
     */
    protected $dataProviderRowPool;

    /**
     * Page action pool
     *
     * @var ActionPool
     */
    protected $actionPool;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param Context $renderContext
     * @param ContentTypeFactory $contentTypeFactory
     * @param ConfigFactory $configFactory
     * @param ConfigBuilderInterface $configBuilder
     * @param DataProviderFactory $dataProviderFactory
     * @param Manager $dataProviderManager
     * @param OptionsFactory $optionsFactory
     * @param ActionPool $actionPool
     * @param RowPool $dataProviderRowPool
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Context $renderContext,
        ContentTypeFactory $contentTypeFactory,
        ConfigFactory $configFactory,
        ConfigBuilderInterface $configBuilder,
        DataProviderFactory $dataProviderFactory,
        Manager $dataProviderManager,
        OptionsFactory $optionsFactory,
        ActionPool $actionPool,
        RowPool $dataProviderRowPool,
        array $data = []
    ) {
        $this->actionPool = $actionPool;
        $this->optionsFactory = $optionsFactory;
        $this->dataProviderRowPool = $dataProviderRowPool;
        parent::__construct(
            $context,
            $renderContext,
            $contentTypeFactory,
            $configFactory,
            $configBuilder,
            $dataProviderFactory,
            $dataProviderManager,
            $data
        );
    }

    /**
     * Prepare custom data
     *
     * @return void
     */
    public function prepare()
    {
        $meta = $this->getMeta();
        $defaultConfigData = $this->getDefaultConfiguration();

        if ($this->hasData('configuration')) {
            $configData = $this->getData('configuration');
            if (!empty($configData['page_actions'])) {
                foreach ($configData['page_actions'] as $key => $action) {
                    $defaultConfigData['page_actions'][$key] = isset($configData['page_actions'])
                        ? array_replace($defaultConfigData['page_actions'][$key], $configData['page_actions'][$key])
                        : $defaultConfigData['page_actions'][$key];
                }
            }
            unset($configData['page_actions']);
            $defaultConfigData = array_merge($defaultConfigData, $configData);
        }

        foreach ($defaultConfigData['page_actions'] as $key => $action) {
            $this->actionPool->add($key, $action, $this);
        }
        unset($defaultConfigData['page_actions']);

        $this->prepareConfiguration($defaultConfigData, $this->getData('name'));
        $this->renderContext->getStorage()->addMeta($this->getData('name'), $meta);
        $this->renderContext->getStorage()->addDataCollection($this->getData('name'), $this->getData('dataSource'));
    }

    /**
     * Render content
     *
     * @param array $data
     * @return string
     */
    public function render(array $data = [])
    {
        $this->initialConfiguration();

        return parent::render($data);
    }

    /**
     * Get meta data
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = $this->getData('meta');
        foreach ($meta['fields'] as $key => $field) {
            if ($field['data_type'] === 'date') {
                $field['date_format'] = $this->_localeDate->getDateTimeFormat(
                    \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM
                );
            }

            if (isset($field[static::OPTIONS_PROVIDER_KEY])) {
                $field['options'] = $this->optionsFactory->create($field[static::OPTIONS_PROVIDER_KEY])
                    ->getOptions(empty($field['options']) ? [] : $field['options']);
            }

            unset($field[static::OPTIONS_PROVIDER_KEY]);
            $meta['fields'][$key] = $field;
        }

        return $meta;
    }

    /**
     * Apply data provider to row data
     *
     * @param array $dataRow
     * @return array
     */
    protected function getDataFromDataProvider(array $dataRow)
    {
        if ($this->hasData(static::ROW_DATA_PROVIDER_KEY)) {
            foreach ($this->getData(static::ROW_DATA_PROVIDER_KEY) as $field => $data) {
                $dataRow[$field] = $this->dataProviderRowPool->get($data['class'])->getData($dataRow);
            }
        }

        return $dataRow;
    }

    /**
     * Get collection items
     *
     * @return array
     */
    public function getCollectionItems()
    {
        $items = [];
        $collection = $this->getDataCollection()->getResultCollection();
        foreach ($collection->getItems() as $item) {
            $actualFields = [];
            $itemsData = $this->getDataFromDataProvider($item->getData());
            foreach (array_keys($this->getData('meta/fields')) as $field) {
                $actualFields[$field] = $itemsData[$field];
            }
            $items[] = $actualFields;
        }

        return $items;
    }

    /**
     * @return \Magento\Framework\Api\CriteriaInterface|\Magento\Framework\Data\CollectionDataSourceInterface
     */
    protected function getDataCollection()
    {
        return $this->renderContext->getStorage()->getDataCollection($this->getName());
    }

    /**
     * Configuration initialization
     *
     * @return void
     */
    protected function initialConfiguration()
    {
        $url = $this->getUrl($this->getData('client_root'));
        $this->renderContext->getStorage()->addGlobalData(
            'client',
            [
                'root' => $url,
                'ajax' => [
                    'data' => [
                        'component' => $this->getNameInLayout(),
                    ],
                ]
            ]
        );
        $this->renderContext->getStorage()->addGlobalData('dump', ['extenders' => []]);

        $collection = $this->getDataCollection()->getResultCollection();
        $totalCount = $collection->getTotalCount();
        $this->renderContext->getStorage()->addDataSource(
            $this->getName(),
            [
                'data' => [
                    'meta_reference' => $this->getName(),
                    'items' => $this->getCollectionItems(),
                    'pages' => ceil($totalCount / $this->renderContext->getRequestParam('limit', 20)),
                    'totalCount' => $totalCount,
                ]
            ]
        );
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return [
            'page_actions' => [
                'add' => [
                    'name' => 'add',
                    'label' => __('Add New'),
                    'class' => 'primary',
                    'url' => $this->getUrl('*/*/new'),
                ],
            ]
        ];
    }
}
