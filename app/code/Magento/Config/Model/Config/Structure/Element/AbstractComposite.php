<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element;

/**
 * Abstract Composite.
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractComposite extends \Magento\Config\Model\Config\Structure\AbstractElement
{
    /**
     * Child elements iterator
     *
     * @var Iterator
     */
    protected $_childrenIterator;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\ModuleManagerInterface $moduleManager
     * @param Iterator $childrenIterator
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\ModuleManagerInterface $moduleManager,
        Iterator $childrenIterator
    ) {
        parent::__construct($storeManager, $moduleManager);
        $this->_childrenIterator = $childrenIterator;
    }

    /**
     * Set flyweight data
     *
     * @param array $data
     * @param string $scope
     * @return void
     */
    public function setData(array $data, $scope)
    {
        parent::setData($data, $scope);
        $children = array_key_exists(
            'children',
            $this->_data
        ) && is_array(
            $this->_data['children']
        ) ? $this->_data['children'] : [];
        $this->_childrenIterator->setElements($children, $scope);
    }

    /**
     * Check whether element has visible child elements
     *
     * @return bool
     */
    public function hasChildren()
    {
        foreach ($this->getChildren() as $child) {
            return (bool)$child;
        }
        return false;
    }

    /**
     * Retrieve children iterator
     *
     * @return \Magento\Config\Model\Config\Structure\Element\Iterator
     */
    public function getChildren()
    {
        return $this->_childrenIterator;
    }

    /**
     * Check whether element is visible
     *
     * @return bool
     */
    public function isVisible()
    {
        if (parent::isVisible()) {
            return $this->hasChildren() || $this->getFrontendModel();
        }
        return false;
    }
}
