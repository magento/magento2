<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Plugin\Eav\System\Config\Source;

/**
 * Plugin for \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype
 *
 * @package Magento\Swatches\Plugin\Eav\System\Config\Source
 */
class InputtypePlugin
{
    /**
     * Append result with additional compatible input types.
     *
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetVolatileInputTypes(
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype $subject,
        array $result
    ) {
        $result = array_merge($result, [['select', 'swatch_visual', 'swatch_text']]);
        return $result;
    }
}
