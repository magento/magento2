<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * Class Paging
 */
class Paging extends AbstractView
{
    /**
     * Prepare component data
     *
     * @return void
     */
    public function prepare()
    {
        $configData = $this->getDefaultConfiguration();
        if ($this->hasData('config')) {
            $configData = array_merge($configData, $this->getData('config'));
        }

        $this->prepareConfiguration($configData);
        $this->updateDataCollection();
    }

    /**
     * Update data collection
     *
     * @return void
     */
    protected function updateDataCollection()
    {
        $defaultPage = $this->config->getData('current');
        $offset = $this->renderContext->getRequestParam('page', $defaultPage);
        $defaultLimit = $this->config->getData('pageSize');
        $size = $this->renderContext->getRequestParam('limit', $defaultLimit);
        $this->renderContext->getStorage()->getDataCollection($this->getParentName())->setLimit($offset, $size);
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
