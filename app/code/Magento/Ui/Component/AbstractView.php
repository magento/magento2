<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\ConfigInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Ui\DataProvider\Factory as DataProviderFactory;
use Magento\Ui\DataProvider\Manager;

/**
 * Abstract class AbstractView
 */
abstract class AbstractView extends Template implements UiComponentInterface
{
    /**
     * Config builder
     *
     * @var ConfigBuilderInterface
     */
    protected $configBuilder;

    /**
     * View configuration data
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Render context
     *
     * @var Context
     */
    protected $renderContext;

    /**
     * Config factory
     *
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * Content type factory
     *
     * @var ContentTypeFactory
     */
    protected $contentTypeFactory;

    /**
     * Asset service
     *
     * @var Repository
     */
    protected $assetRepo;

    /**
     * Data provider factory
     *
     * @var DataProviderFactory
     */
    protected $dataProviderFactory;

    /**
     * @var \Magento\Ui\DataProvider\Manager
     */
    protected $dataManager;

    /**
     * Elements for the render
     *
     * @var ElementRendererInterface[]
     */
    protected $elements = [];

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
        array $data = []
    ) {
        $this->renderContext = $renderContext;
        $this->contentTypeFactory = $contentTypeFactory;
        $this->assetRepo = $context->getAssetRepository();
        $this->configFactory = $configFactory;
        $this->configBuilder = $configBuilder;
        $this->dataProviderFactory = $dataProviderFactory;
        $this->dataManager = $dataProviderManager;
        parent::__construct($context, $data);
    }

    /**
     * Update data
     *
     * @param array $data
     * @return void
     */
    public function update(array $data = [])
    {
        if (!empty($data)) {
            $this->_data = array_merge_recursive($this->_data, $data);
        }
    }

    /**
     * Prepare component data
     *
     * @return void
     */
    public function prepare()
    {
        //
    }

    /**
     * Render content
     *
     * @param array $data
     * @return string
     */
    public function render(array $data = [])
    {
        $prevData = $this->getData();
        $this->update($data);

        $renderResult = $this->contentTypeFactory->get($this->renderContext->getAcceptType())
            ->render($this, $this->getContentTemplate());

        $this->setData($prevData);

        return $renderResult;
    }

    /**
     * Render label
     *
     * @return mixed|string
     */
    public function renderLabel()
    {
        return $this->contentTypeFactory->get($this->renderContext->getAcceptType())
            ->render($this, $this->getLabelTemplate());
    }

    /**
     * Render element
     *
     * @param string $elementName
     * @param array $arguments
     * @return mixed|string
     */
    public function renderElement($elementName, array $arguments)
    {
        $element = $this->renderContext->getRender()->getUiElementView($elementName);
        $result = $element->render($arguments);
        return $result;
    }

    /**
     * Render component label
     *
     * @param string $elementName
     * @param array $arguments
     * @return string
     */
    public function renderElementLabel($elementName, array $arguments)
    {
        $element = $this->renderContext->getRender()->getUiElementView($elementName);
        $prevData = $element->getData();
        $element->update($arguments);
        $result = $element->renderLabel();
        $element->setData($prevData);
        return $result;
    }

    /**
     * Shortcut for rendering as HTML
     * (used for backward compatibility with standard rendering mechanism via layout interface)
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Getting label template
     *
     * @return string|false
     */
    public function getLabelTemplate()
    {
        return 'Magento_Ui::label/default.phtml';
    }

    /**
     * Getting content template
     *
     * @return string|false
     */
    public function getContentTemplate()
    {
        return $this->getData('content_template');
    }

    /**
     * Get Layout Node
     *
     * @param string $fullName
     * @param mixed $default
     * @return array
     */
    public function getLayoutElement($fullName, $default = null)
    {
        return $this->renderContext->getStorage()->getLayoutNode($fullName, $default);
    }

    /**
     * Get name component instance
     *
     * @return string
     */
    public function getName()
    {
        return $this->config->getName();
    }

    /**
     * Get parent name component instance
     *
     * @return string
     */
    public function getParentName()
    {
        return $this->config->getParentName();
    }

    /**
     * Get configuration builder
     *
     * @return ConfigBuilderInterface
     */
    public function getConfigBuilder()
    {
        return $this->configBuilder;
    }

    /**
     * Set component configuration
     *
     * @param null $configData
     * @param null $name
     * @param null $parentName
     * @return void
     */
    public function prepareConfiguration($configData = null, $name = null, $parentName = null)
    {
        $arguments = [];
        $arguments['name'] = $name ?: $this->renderContext->getNamespace() . '_' . $this->getNameInLayout();
        $arguments['parentName'] = $parentName ?: $this->renderContext->getNamespace();
        if ($configData) {
            $arguments['configuration'] = $configData;
        }
        $this->config = $this->configFactory->create($arguments);
        $this->renderContext->getStorage()->addComponentsData($this->config);
    }

    /**
     * Get component configuration
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get render context
     *
     * @return Context
     */
    public function getRenderContext()
    {
        return $this->renderContext;
    }

    /**
     * Get elements to the render
     *
     * @return ElementRendererInterface[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Set elements for the render
     *
     * @param ElementRendererInterface[] $elements
     * @return mixed|void
     */
    public function setElements(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return [];
    }

    /**
     * Get render engine
     *
     * @return \Magento\Ui\ContentType\ContentTypeInterface
     */
    protected function getRenderEngine()
    {
        return $this->contentTypeFactory->get($this->renderContext->getAcceptType());
    }

    /**
     * Create data provider
     *
     * @return void
     */
    protected function createDataProviders()
    {
        if ($this->hasData('data_provider_pool')) {
            foreach ($this->getData('data_provider_pool') as $name => $config) {
                $arguments = empty($config['arguments']) ? [] : $config['arguments'];
                $arguments['params'] = $this->renderContext->getRequestParams();

                $dataProvider = $this->dataProviderFactory->create($config['class'], $arguments);
                $this->renderContext->getStorage()->addDataProvider($name, $dataProvider);
            }
        }
    }
}
