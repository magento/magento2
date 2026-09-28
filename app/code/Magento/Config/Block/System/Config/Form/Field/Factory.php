<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Config\Block\System\Config\Form\Field;

/**
 * Magento\Config\Block\System\Config\Form\Field Class Factory
 *
 * @api
 * @codeCoverageIgnore
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param array $data
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Config\Block\System\Config\Form\Field::class, $data);
    }
}
