<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Adminhtml\System\Config\Source;

class Inputtype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    private $optionsArray;

    /**
     * Inputtype constructor.
     * @param array $optionsArray
     */
    public function __construct(array $optionsArray = [])
    {
        $this->optionsArray = $optionsArray;
    }

    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        //sort array elements using key value
        ksort($this->optionsArray);
        return $this->optionsArray;
    }

    /**
     * Get volatile input types.
     *
     * @return array
     */
    public function getVolatileInputTypes()
    {
        return [
            ['textarea', 'texteditor'],
        ];
    }

    /**
     * Get hint for input types
     *
     * @return array
     */
    public function getInputTypeHints()
    {
        return [
            'texteditor' => __(
                'Text Editor input type requires WYSIWYG to be enabled in Stores > Configuration > Content Management.'
            ),
        ];
    }
}
