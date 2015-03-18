<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * Class Paging
 */
class Paging extends AbstractComponent
{
    const NAME = 'paging';

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
     * Prepare component data
     *
     * @return void
     */
    public function prepare()
    {
        $this->prepareConfiguration();

        $defaultPage = $this->getData('config/current') ?: 1;
        $defaultLimit = $this->getData('config/pageSize') ?: 20;
        $paging = $this->getContext()->getRequestParam('paging');

        $offset = isset($paging['current']) ? $paging['current'] : $defaultPage;
        $size = isset($paging['pageSize']) ? $paging['pageSize'] : $defaultLimit;

        $this->getContext()->getDataProvider()->setLimit($offset, $size);

        $jsConfig = $this->getJsConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return  [
            'sizes' => [20, 30, 50, 100, 200],
            'pageSize' => 20,
            'current' => 1
        ];
    }
}
