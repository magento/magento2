<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\Control\ActionPoolFactory;
use Magento\Framework\View\Element\UiComponent\Control\ActionPoolInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderFactory;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\LayoutInterface as PageLayoutInterface;

/**
 * Class Context
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Context implements ContextInterface
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * Application request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Factory renderer for a content type
     *
     * @var ContentTypeFactory
     */
    protected $contentTypeFactory;

    /**
     * Accept type
     *
     * @var string
     */
    protected $acceptType;

    /**
     * @var PageLayoutInterface
     */
    protected $pageLayout;

    /**
     * @var ButtonProviderFactory
     */
    protected $buttonProviderFactory;

    /**
     * @var ActionPoolInterface
     */
    protected $actionPool;

    /**
     * Registry components
     *
     * @var array
     */
    protected $componentsDefinitions = [];

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @param PageLayoutInterface $pageLayout
     * @param RequestInterface $request
     * @param ButtonProviderFactory $buttonProviderFactory
     * @param ActionPoolFactory $actionPoolFactory
     * @param ContentTypeFactory $contentTypeFactory
     * @param UrlInterface $urlBuilder
     * @param Processor $processor
     * @param DataProviderInterface|null $dataProvider
     * @param null $namespace
     */
    public function __construct(
        PageLayoutInterface $pageLayout,
        RequestInterface $request,
        ButtonProviderFactory $buttonProviderFactory,
        ActionPoolFactory $actionPoolFactory,
        ContentTypeFactory $contentTypeFactory,
        UrlInterface $urlBuilder,
        Processor $processor,
        DataProviderInterface $dataProvider = null,
        $namespace = null
    ) {
        $this->namespace = $namespace;
        $this->request = $request;
        $this->buttonProviderFactory = $buttonProviderFactory;
        $this->dataProvider = $dataProvider;
        $this->pageLayout = $pageLayout;
        $this->actionPool = $actionPoolFactory->create(['context' => $this]);
        $this->contentTypeFactory = $contentTypeFactory;
        $this->urlBuilder = $urlBuilder;
        $this->processor = $processor;
        $this->setAcceptType();
    }

    /**
     * Add component into registry
     *
     * @param string $name
     * @param array $config
     * @return void
     */
    public function addComponentDefinition($name, array $config)
    {
        if (!isset($this->componentsDefinitions[$name])) {
            $this->componentsDefinitions[$name] = $config;
        }
    }

    /**
     * To get the registry components
     *
     * @return array
     */
    public function getComponentsDefinitions()
    {
        return $this->componentsDefinitions;
    }

    /**
     * Get render engine
     *
     * @return ContentTypeInterface
     */
    public function getRenderEngine()
    {
        return $this->contentTypeFactory->get($this->getAcceptType());
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Getting accept type
     *
     * @return string
     */
    public function getAcceptType()
    {
        return $this->acceptType;
    }

    /**
     * Getting all request data
     *
     * @return mixed
     */
    public function getRequestParams()
    {
        return $this->request->getParams();
    }

    /**
     * Getting data according to the key
     *
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getRequestParam($key, $defaultValue = null)
    {
        return $this->request->getParam($key, $defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltersParams()
    {
        return $this->getRequestParam(self::FILTER_VAR, []);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterParam($key, $defaultValue = null)
    {
        $filter = $this->getFiltersParams();
        return isset($filter[$key]) ? $filter[$key] : $defaultValue;
    }

    /**
     * Get data provider
     *
     * @return DataProviderInterface
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * @param UiComponentInterface $component
     * @return array
     */
    public function getDataSourceData(UiComponentInterface $component)
    {
        $dataSource = $component->getDataSourceData();
        $this->prepareDataSource($dataSource, $component);
        $dataProviderConfig = $this->getDataProvider()->getConfigData();
        return [
            $this->getDataProvider()->getName() => [
                'type' => 'dataSource',
                'name' => $this->getDataProvider()->getName(),
                'dataScope' => $this->getNamespace(),
                'config' => array_replace_recursive(
                    array_merge($dataSource, $dataProviderConfig),
                    [
                        'params' => [
                            'namespace' => $this->getNamespace()
                        ],
                    ]
                )
            ]
        ];
    }

    /**
     * Get page layout
     *
     * @return PageLayoutInterface
     */
    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * Add button in the actions toolbar
     *
     * @param array $buttons
     * @param UiComponentInterface $component
     * @return void
     */
    public function addButtons(array $buttons, UiComponentInterface $component)
    {
        if (!empty($buttons)) {
            foreach ($buttons as $buttonId => $buttonData) {
                if (is_array($buttonData)) {
                    $buttons[$buttonId] = $buttonData;
                    continue;
                }
                /** @var ButtonProviderInterface $button */
                $button = $this->buttonProviderFactory->create($buttonData);
                $buttonData = $button->getButtonData();
                if (!$buttonData) {
                    unset($buttons[$buttonId]);
                    continue;
                }
                $buttons[$buttonId] = $buttonData;
            }
            uasort($buttons, [$this, 'sortButtons']);

            foreach ($buttons as $buttonId => $buttonData) {
                if (isset($buttonData['url'])) {
                    $buttonData['url'] = $this->getUrl($buttonData['url']);
                }
                $this->actionPool->add($buttonId, $buttonData, $component);
            }
        }
    }

    /**
     * Sort buttons by sort order
     *
     * @param array $itemA
     * @param array $itemB
     * @return int
     */
    public function sortButtons(array $itemA, array $itemB)
    {
        $sortOrderA = isset($itemA['sort_order']) ? intval($itemA['sort_order']) : 0;
        $sortOrderB = isset($itemB['sort_order']) ? intval($itemB['sort_order']) : 0;

        return $sortOrderA - $sortOrderB;
    }

    /**
     * Getting requested accept type
     *
     * @return void
     */
    protected function setAcceptType()
    {
        $this->acceptType = 'html';

        $rawAcceptType = $this->request->getHeader('Accept');
        if ($this->request->getParam('isAjax') === 'true' || strpos($rawAcceptType, 'json') !== false) {
            $this->acceptType = 'json';
        } else if (strpos($rawAcceptType, 'html') !== false) {
            $this->acceptType = 'html';
        } else if (strpos($rawAcceptType, 'xml') !== false) {
            $this->acceptType = 'xml';
        }
    }

    /**
     * Set data provider
     *
     * @param DataProviderInterface $dataProvider
     * @return void
     */
    public function setDataProvider(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Call `prepareData` method of all the components
     *
     * @param array $data
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareDataSource(array & $data, UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareDataSource($data, $child);
            }
        }
        $data = $component->prepareDataSource($data);
    }

    /**
     * @inheritDoc
     */
    public function getProcessor()
    {
        return $this->processor;
    }
}
