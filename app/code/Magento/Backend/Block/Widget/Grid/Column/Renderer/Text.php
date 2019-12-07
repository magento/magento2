<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Backend grid item renderer
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Text extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Format variables pattern
     *
     * @var string
     */
    protected $_variablePattern = '/\\$([a-z0-9_]+)/i';

    /**
     * Get value for the cel
     *
     * @param DataObject $row
     * @return string
     */
    public function _getValue(DataObject $row)
    {
        if (null === $this->getColumn()->getFormat()) {
            return $this->getSimpleValue($row);
        }
        return $this->getFormattedValue($row);
    }

    /**
     * Get simple value
     *
     * @param DataObject $row
     * @return string
     */
    private function getSimpleValue(DataObject $row)
    {
        $data = parent::_getValue($row);
        $value = null === $data ? $this->getColumn()->getDefault() : $data;
        if (true === $this->getColumn()->getTranslate()) {
            $value = __($value);
        }
        return $this->escapeHtml($value);
    }

    /**
     * Replace placeholders in the string with values
     *
     * @param DataObject $row
     * @return string
     */
    private function getFormattedValue(DataObject $row)
    {
        $value = $this->getColumn()->getFormat() ?: null;
        if (true === $this->getColumn()->getTranslate()) {
            $value = __($value);
        }
        if (preg_match_all($this->_variablePattern, $value, $matches)) {
            foreach ($matches[0] as $index => $match) {
                $replacement = $row->getData($matches[1][$index]);
                $value = str_replace($match, $replacement, $value);
            }
        }
        return $this->escapeHtml($value);
    }
}
