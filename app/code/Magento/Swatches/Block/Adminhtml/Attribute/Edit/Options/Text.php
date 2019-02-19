<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options;

/**
 * Block Class for Text Swatch
 *
 * @api
 * @since 100.0.2
 */
class Text extends AbstractSwatch
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Swatches::catalog/product/attribute/text.phtml';

    /**
     * Return json config for text option JS initialization
     *
     * @return array
     * @since 100.1.0
     */
    public function getJsonConfig()
    {
        $values = [];
        foreach ($this->getOptionValues() as $value) {
            $values[] = $value->getData();
        }

        $data = [
            'attributesData' => $values,
            'isSortable' => (int)(!$this->getReadOnly() && !$this->canManageOptionDefaultOnly()),
            'isReadOnly' => (int)$this->getReadOnly()
        ];

        return json_encode($data);
    }
}
