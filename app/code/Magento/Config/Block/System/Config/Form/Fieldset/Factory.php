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
     * @return \Magento\Config\Block\System\Config\Form\Fieldset
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Config\Block\System\Config\Form\Fieldset::class, $data);
    }
}
