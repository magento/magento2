<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component;

/**
 * Class Sorting
 */
class Sorting extends AbstractView
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
        $field = $this->config->getData('field');
        $direction = $this->config->getData('direction');
        if (!empty($field) && !empty($direction)) {
            $this->renderContext->getStorage()->getDataCollection($this->getParentName())->addOrder(
                $this->renderContext->getRequestParam('sort', $field),
                strtoupper($this->renderContext->getRequestParam('dir', $direction))
            );
        }
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return ['direction' => 'asc'];
    }
}
