<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;

class OptionTypesList implements \Magento\ConfigurableProduct\Api\OptionTypesListInterface
{
    /**
     * @var \Magento\Catalog\Model\System\Config\Source\Inputtype
     */
    protected $inputType;

    /**
     * @param \Magento\Catalog\Model\System\Config\Source\Inputtype $inputType
     */
    public function __construct(\Magento\Catalog\Model\System\Config\Source\Inputtype $inputType)
    {
        $this->inputType = $inputType;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array_map(
            function ($inputType) {
                return $inputType['value'];
            },
            $this->inputType->toOptionArray()
        );
    }
}
