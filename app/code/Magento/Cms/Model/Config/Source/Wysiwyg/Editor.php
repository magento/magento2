<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
