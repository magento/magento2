<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\Control\ActionPoolFactory;
use Magento\Ui\Component\Control\ActionPoolInterface;
use Magento\Ui\Component\Control\ButtonProviderFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Control\ButtonProviderInterface;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\LayoutInterface as PageLayoutInterface;

/**
 * Class Context
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
     * Config provider
     *
     * @var ConfigProviderInterface
     */
    protected $configProvider;

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
     * @param PageLayoutInterface $pageLayout
     * @param RequestInterface $request
     * @param ButtonProviderFactory $buttonProviderFactory
     * @param ActionPoolFactory $actionPoolFactory
     * @param ContentTypeFactory $contentTypeFactory
     * @param DataProviderInterface|null $dataProvider
     * @param string $namespace
     */
    public function __construct(
        PageLayoutInterface $pageLayout,
        RequestInterface $request,
        ButtonProviderFactory $buttonProviderFactory,
        ActionPoolFactory $actionPoolFactory,
        ContentTypeFactory $contentTypeFactory,
        DataProviderInterface $dataProvider = null,
        $namespace = null
    ) {
        $this->namespace = $namespace;
        $this->request = $request;
        $this->buttonProviderFactory = $buttonProviderFactory;
        $this->dataProvider = $dataProvider;
        $this->pageLayout = $pageLayout;
        $this->actionPool = $actionPoolFactory->create(
            [
                'context' => $this
            ]
        );
        $this->contentTypeFactory = $contentTypeFactory;

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
     * Get data provider
     *
     * @return DataProviderInterface
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
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
        $this->acceptType = 'xml';

        $rawAcceptType = $this->request->getHeader('Accept');
        if ($this->request->getParam('isAjax') === 'true' || strpos($rawAcceptType, 'json') !== false) {
            $this->acceptType = 'json';
        } elseif (strpos($rawAcceptType, 'html') !== false) {
            $this->acceptType = 'html';
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
}
