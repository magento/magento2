<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component;

/**
 * Class MassAction
 */
class MassAction extends AbstractView
{
    /**
     * Prepare component data
     *
     * @return $this|void
     */
    public function prepare()
    {
        $configData = $this->getDefaultConfiguration();
        if ($this->hasData('config')) {
            $configData = array_merge($configData, $this->getData('config'));
        }
        array_walk_recursive(
            $configData,
            function (&$item, $key, $object) {
                if ($key === 'url') {
                    $item = $object->getUrl($item);
                }
            },
            $this
        );

        $this->prepareConfiguration($configData);
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return ['actions' => []];
    }
}
