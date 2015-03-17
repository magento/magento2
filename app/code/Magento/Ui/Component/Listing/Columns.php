<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

use Magento\Ui\Component\AbstractComponent;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Columns
 */
class Columns extends AbstractComponent
{
    const NAME = 'columns';

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
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        foreach ($this->getChildComponents() as $column) {
            if ($column instanceof Column) {
                $meta = $this->getContext()->getDataProvider()->getFieldMetaInfo($this->getName(), $column->getName());
                if ($meta) {
                    $config = $column->getData('config');
                    $config = array_replace_recursive($config, $meta);
                    $column->setData('config', $config);
                }
            }
        }

        $jsConfig = $this->getJsConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}
