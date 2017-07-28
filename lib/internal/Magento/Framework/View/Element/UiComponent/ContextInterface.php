<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\LayoutInterface as PageLayoutInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Interface ContextInterface
 * @since 2.0.0
 */
interface ContextInterface
{
    /**
     * Filter variable name
     */
    const FILTER_VAR = 'filters';

    /**
     * Add components definition
     *
     * @param string $name
     * @param array $config
     * @return void
     * @since 2.0.0
     */
    public function addComponentDefinition($name, array $config);

    /**
     * Get components definitions
     *
     * @return array
     * @since 2.0.0
     */
    public function getComponentsDefinitions();

    /**
     * Getting root component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getNamespace();

    /**
     * Getting accept type
     *
     * @return string
     * @since 2.0.0
     */
    public function getAcceptType();

    /**
     * Set data provider
     *
     * @param DataProviderInterface $dataProvider
     * @return void
     * @since 2.0.0
     */
    public function setDataProvider(DataProviderInterface $dataProvider);

    /**
     * Get data provider
     *
     * @return DataProviderInterface
     * @since 2.0.0
     */
    public function getDataProvider();

    /**
     * Get Data Source array
     *
     * @param UiComponentInterface $component
     * @return array
     * @since 2.0.0
     */
    public function getDataSourceData(UiComponentInterface $component);

    /**
     * Getting all request data
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getRequestParams();

    /**
     * Getting data according to the key
     *
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed
     * @since 2.0.0
     */
    public function getRequestParam($key, $defaultValue = null);

    /**
     * Get filters params
     *
     * @return array
     * @since 2.0.0
     */
    public function getFiltersParams();

    /**
     * Get filter params according to the key
     *
     * @param string $key
     * @param null|string $defaultValue
     * @return mixed|null
     * @since 2.0.0
     */
    public function getFilterParam($key, $defaultValue = null);

    /**
     * Get root layout
     *
     * @return PageLayoutInterface
     * @since 2.0.0
     */
    public function getPageLayout();

    /**
     * Add button in the actions toolbar
     *
     * @param array $buttons
     * @param UiComponentInterface $component
     * @return void
     * @since 2.0.0
     */
    public function addButtons(array $buttons, UiComponentInterface $component);

    /**
     * Add html block in the actions toolbar
     *
     * @param array $htmlBlocks
     * @param UiComponentInterface $component
     * @return void
     * @since 2.1.0
     */
    public function addHtmlBlocks(array $htmlBlocks, UiComponentInterface $component);

    /**
     * Get render engine
     *
     * @return ContentTypeInterface
     * @since 2.0.0
     */
    public function getRenderEngine();

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     * @since 2.0.0
     */
    public function getUrl($route = '', $params = []);

    /**
     * Get component processor
     *
     * @return Processor
     * @since 2.0.0
     */
    public function getProcessor();

    /**
     * Get Ui Component Factory
     *
     * @return UiComponentFactory
     * @since 2.1.0
     */
    public function getUiComponentFactory();
}
