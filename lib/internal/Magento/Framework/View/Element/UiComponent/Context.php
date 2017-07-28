<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\Control\ActionPoolFactory;
use Magento\Framework\View\Element\UiComponent\Control\ActionPoolInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderFactory;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\LayoutInterface as PageLayoutInterface;

/**
 * Class Context
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Context implements ContextInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $namespace;

    /**
     * @var DataProviderInterface
     * @since 2.0.0
     */
    protected $dataProvider;

    /**
     * Application request
     *
     * @var RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * Factory renderer for a content type
     *
     * @var ContentTypeFactory
     * @since 2.0.0
     */
    protected $contentTypeFactory;

    /**
     * Accept type
     *
     * @var string
     * @since 2.0.0
     */
    protected $acceptType;

    /**
     * @var PageLayoutInterface
     * @since 2.0.0
     */
    protected $pageLayout;

    /**
     * @var ButtonProviderFactory
     * @since 2.0.0
     */
    protected $buttonProviderFactory;

    /**
     * @var ActionPoolInterface
     * @since 2.0.0
     */
    protected $actionPool;

    /**
     * Registry components
     *
     * @var array
     * @since 2.0.0
     */
    protected $componentsDefinitions = [];

    /**
     * Url Builder
     *
     * @var UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @var Processor
     * @since 2.0.0
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
     * @param UiComponentFactory $uiComponentFactory
     * @param DataProviderInterface|null $dataProvider
     * @param null $namespace
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        PageLayoutInterface $pageLayout,
        RequestInterface $request,
        ButtonProviderFactory $buttonProviderFactory,
        ActionPoolFactory $actionPoolFactory,
        ContentTypeFactory $contentTypeFactory,
        UrlInterface $urlBuilder,
        Processor $processor,
        UiComponentFactory $uiComponentFactory,
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
        $this->uiComponentFactory = $uiComponentFactory;
        $this->setAcceptType();
    }

    /**
     * Add component into registry
     *
     * @param string $name
     * @param array $config
     * @return void
     * @since 2.0.0
     */
    public function addComponentDefinition($name, array $config)
    {
        if (!isset($this->componentsDefinitions[$name])) {
            $this->componentsDefinitions[$name] = $config;
        } else {
            $this->componentsDefinitions[$name] = array_merge(
                $this->componentsDefinitions[$name],
                $config
            );
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getComponentsDefinitions()
    {
        return $this->componentsDefinitions;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRenderEngine()
    {
        return $this->contentTypeFactory->get($this->getAcceptType());
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAcceptType()
    {
        return $this->acceptType;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRequestParams()
    {
        return $this->request->getParams();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRequestParam($key, $defaultValue = null)
    {
        return $this->request->getParam($key, $defaultValue);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFiltersParams()
    {
        return $this->getRequestParam(self::FILTER_VAR, []);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFilterParam($key, $defaultValue = null)
    {
        $filter = $this->getFiltersParams();
        return isset($filter[$key]) ? $filter[$key] : $defaultValue;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function sortButtons(array $itemA, array $itemB)
    {
        $sortOrderA = isset($itemA['sort_order']) ? intval($itemA['sort_order']) : 0;
        $sortOrderB = isset($itemB['sort_order']) ? intval($itemB['sort_order']) : 0;

        return $sortOrderA - $sortOrderB;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.1.0
     */
    public function addHtmlBlocks(array $htmlBlocks, UiComponentInterface $component)
    {
        if (!empty($htmlBlocks)) {
            foreach ($htmlBlocks as $htmlBlock => $blockData) {
                $this->actionPool->addHtmlBlock($blockData['type'], $blockData['name'], $blockData['arguments']);
            }
        }
    }

    /**
     * Getting requested accept type
     *
     * @return void
     * @since 2.0.0
     */
    protected function setAcceptType()
    {
        $this->acceptType = 'html';

        $rawAcceptType = $this->request->getHeader('Accept');
        if (strpos($rawAcceptType, 'json') !== false) {
            $this->acceptType = 'json';
        } elseif (strpos($rawAcceptType, 'html') !== false) {
            $this->acceptType = 'html';
        } elseif (strpos($rawAcceptType, 'xml') !== false) {
            $this->acceptType = 'xml';
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDataProvider(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getUiComponentFactory()
    {
        return $this->uiComponentFactory;
    }
}
