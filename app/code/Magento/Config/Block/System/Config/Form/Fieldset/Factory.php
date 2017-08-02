<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Block\System\Config\Form\Fieldset;

/**
 * Magento\Config\Block\System\Config\Form\Fieldset Class Factory
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param array $data
     * @return \Magento\Config\Block\System\Config\Form\Fieldset
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Config\Block\System\Config\Form\Fieldset::class, $data);
    }
}
