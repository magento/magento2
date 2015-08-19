<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

use Magento\Ui\Component\AbstractComponent;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;

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
        $this->buildUrlsForInlineEditing();
        parent::prepare();
    }

    /**
     * Build urls for inline editing
     *
     * @return void
     */
    protected function buildUrlsForInlineEditing()
    {
        $config = $this->getConfiguration();
        if (isset($config['editorConfig']) && isset($config['editorConfig']['clientConfig'])) {
            foreach ($config['editorConfig']['clientConfig'] as $key => &$value) {
                if (in_array($key, ['saveUrl', 'validateUrl'])) {
                    $value = $this->getContext()->getUrl($value);
                }
            }
        }
        $this->setData('config', $config);
    }
}
