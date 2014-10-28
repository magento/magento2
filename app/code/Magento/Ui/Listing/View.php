<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui\Listing;

use Magento\Ui\AbstractView;
use Magento\Ui\Control\ActionPool;
use \Magento\Ui\DataProvider\RowPool;
use Magento\Ui\DataProvider\OptionsFactory;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class View
 */
class View extends AbstractView
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
     * @param OptionsFactory $optionsFactory
     * @param ActionPool $actionPool
     * @param RowPool $dataProviderRowPool
     * @param ConfigFactory $configFactory
     * @param ConfigBuilderInterface $configBuilder
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Context $renderContext,
        ContentTypeFactory $contentTypeFactory,
        ConfigFactory $configFactory,
        ConfigBuilderInterface $configBuilder,
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
        $config = $this->getDefaultConfiguration();

        if ($this->hasData('configuration')) {
            $configuration = $this->getData('configuration');
            if (!empty($configuration['page_actions'])) {
                foreach ($configuration['page_actions'] as $key => $action) {
                    $config['page_actions'][$key] = isset($configuration['page_actions'])
                        ? array_replace($config['page_actions'][$key], $configuration['page_actions'][$key])
                        : $config['page_actions'][$key];
                }
            }
            unset($configuration['page_actions']);
            $config = array_merge($config, $configuration);
        }

        foreach ($config['page_actions'] as $key => $action) {
            $this->actionPool->add($key, $action, $this);
        }
        unset($config['page_actions']);

        $this->configuration = $this->configurationFactory->create(
            [
                'name' => $this->getData('name'),
                'parentName' => $this->getData('name'),
                'configuration' => $config
            ]
        );
        $this->renderContext->getStorage()->addComponentsData($this->configuration);
        $this->renderContext->getStorage()->addMeta($this->getData('name'), $meta);
        $this->renderContext->getStorage()->addDataCollection($this->getData('name'), $this->getData('dataSource'));
    }

    /**
     * Render view
     *
     * @return mixed|string
     */
    public function render()
    {
        $this->initialConfiguration();

        return parent::render();
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

            // TODO fixme
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
        $collection = $this->renderContext->getStorage()->getDataCollection($this->getName());
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
     * Configuration initialization
     *
     * @return void
     */
    protected function initialConfiguration()
    {
        $this->renderContext->getStorage()->addGlobalData(
            'client',
            [
                'root' => $this->getUrl($this->getData('client_root')),
                'ajax' => [
                    'data' => [
                        'component' => $this->getNameInLayout()
                    ]
                ]
            ]
        );
        $this->renderContext->getStorage()->addGlobalData('dump', ['extenders' => []]);

        $countItems = $this->renderContext->getStorage()->getDataCollection($this->getName())->getSize();
        $this->renderContext->getStorage()->addData(
            $this->getName(),
            [
                'meta_reference' => $this->getName(),
                'items' => $this->getCollectionItems(),
                'pages' => ceil($countItems / $this->renderContext->getRequestParam('limit', 20)),
                'totalCount' => $countItems
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
                    'url' => $this->getUrl('*/*/new')
                ]
            ]
        ];
    }
}
