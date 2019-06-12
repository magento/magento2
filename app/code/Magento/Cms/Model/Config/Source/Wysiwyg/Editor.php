<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Config\Source\Wysiwyg;

/**
 * Configuration source model for Wysiwyg toggling
 */
class Editor implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    private $adapterOptions;

    /**
     * @param array $adapterOptions
     */
    public function __construct(array $adapterOptions = [])
    {
        $this->adapterOptions = $adapterOptions;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->adapterOptions;
    }
}
