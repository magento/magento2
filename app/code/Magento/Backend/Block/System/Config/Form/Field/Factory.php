<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * \Magento\Backend\Block\System\Config\Form\Field object factory
 */
namespace Magento\Backend\Block\System\Config\Form\Field;

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
     * @return \Magento\Backend\Block\System\Config\Form\Field
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create('Magento\Backend\Block\System\Config\Form\Field', $data);
    }
}
