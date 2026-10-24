<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;
use Magento\Ui\Component\Listing\Columns\Options as UiOptions;

/**
 * Grid column widget for rendering grid cells that contains mapped values
 *
 * @api
 * @deprecated 100.2.0 Legacy grid renderer; use UI component columns instead.
 * @see UiOptions
 * @since 100.0.2
 */
class Options extends Text
{
    /**
     * Get options from column
     *
     * @return array
     */
    protected function _getOptions()
    {
        return $this->getColumn()->getOptions();
    }

    /**
     * Render a grid cell as options
     *
     * @param DataObject $row
     * @return string|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function render(DataObject $row)
    {
        $options = $this->_getOptions();

        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            //transform option format
            $output = [];
            foreach ($options as $option) {
                $output[$option['value']] = $option['label'];
            }

            $value = $row->getData($this->getColumn()->getIndex());
            if (is_array($value)) {
                $res = [];
                foreach ($value as $item) {
                    if ($item !== null && isset($output[$item])) {
                        $res[] = $this->escapeHtml($output[$item]);
                    } elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            } elseif ($value !== null && isset($output[$value])) {
                return $this->escapeHtml($output[$value]);
            } elseif ($value !== null && in_array($value, $output)) {
                return $this->escapeHtml($value);
            }
        }
    }
}
