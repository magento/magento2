<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Class Form
 */
class Form extends AbstractComponent
{
    const NAME = 'form';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        unset($jsConfig['extends']);
        $this->getContext()->addComponentDefinition($this->getContext()->getNamespace(), $jsConfig);

        $this->getContext()->addButtons($this->getData('buttons'), $this);
    }

    /**
     * @return array
     */
    public function getDataSourceData()
    {
        $dataSources = [];
        foreach ($this->getChildComponents() as $component) {
            if ($component instanceof DataSourceInterface) {
                $dataProvider = $component->getDataProvider();
                $id = $this->getContext()->getRequestParam($dataProvider->getRequestFieldName());
                if ($id) {
                    $dataProvider->addFilter($dataProvider->getPrimaryFieldName(), $id);
                    $preparedData = $dataProvider->getData();
                    $preparedData = isset($preparedData[$id]) ? $preparedData[$id] : [];
                } else {
                    $preparedData = [];
                }
                $config = $dataProvider->getConfigData();
                if (isset($config['submit_url'])) {
                    $config['submit_url'] = $this->getContext()->getUrl($config['submit_url']);
                }
                if (isset($config['validate_url'])) {
                    $config['validate_url'] = $this->getContext()->getUrl($config['validate_url']);
                }
                $dataSources[$component->getName()] = [
                    'type' => $component->getComponentName(),
                    'name' => $component->getName(),
                    'dataScope' => $component->getContext()->getNamespace(),
                    'config' => array_merge(['data' => $preparedData], $config)
                ];
            }
        }
        return $dataSources;
    }
}
