<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options;

/**
 * Block Class for Text Swatch
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
