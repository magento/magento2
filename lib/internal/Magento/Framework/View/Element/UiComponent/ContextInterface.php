<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\LayoutInterface as PageLayoutInterface;

/**
 * Interface ContextInterface
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
     */
    public function addComponentDefinition($name, array $config);

    /**
     * Get components definitions
     *
     * @return array
     */
    public function getComponentsDefinitions();

    /**
     * Getting root component name
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Getting accept type
     *
     * @return string
     */
    public function getAcceptType();

    /**
     * Set data provider
     *
     * @param DataProviderInterface $dataProvider
     * @return void
     */
    public function setDataProvider(DataProviderInterface $dataProvider);

    /**
     * Get data provider
     *
     * @return DataProviderInterface
     */
    public function getDataProvider();

    /**
     * Get Data Source array
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getDataSourceData(UiComponentInterface $component);

    /**
     * Getting all request data
     *
     * @return mixed
     */
    public function getRequestParams();

    /**
     * Getting data according to the key
     *
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getRequestParam($key, $defaultValue = null);

    /**
     * Get filters params
     *
     * @return array
     */
    public function getFiltersParams();

    /**
     * Get filter params according to the key
     *
     * @param string $key
     * @param null|string $defaultValue
     * @return mixed|null
     */
    public function getFilterParam($key, $defaultValue = null);

    /**
     * Get root layout
     *
     * @return PageLayoutInterface
     */
    public function getPageLayout();

    /**
     * Add button in the actions toolbar
     *
     * @param array $buttons
     * @param UiComponentInterface $component
     * @return void
     */
    public function addButtons(array $buttons, UiComponentInterface $component);

    /**
     * Get render engine
     *
     * @return ContentTypeInterface
     */
    public function getRenderEngine();

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = []);

    /**
     * Get component processor
     *
     * @return Processor
     */
    public function getProcessor();
}
