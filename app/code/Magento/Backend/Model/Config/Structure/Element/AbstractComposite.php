<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element;

abstract class AbstractComposite extends \Magento\Backend\Model\Config\Structure\AbstractElement
{
    /**
     * Child elements iterator
     *
     * @var Iterator
     */
    protected $_childrenIterator;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Iterator $childrenIterator
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, Iterator $childrenIterator)
    {
        parent::__construct($storeManager);
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
     * @return \Magento\Backend\Model\Config\Structure\Element\Iterator
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
