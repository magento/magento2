<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model\Config\Structure\Element;

abstract class AbstractComposite
    extends \Magento\Backend\Model\Config\Structure\AbstractElement
{
    /**
     * Child elements iterator
     *
     * @var \Magento\Backend\Model\Config\Structure\Element\Iterator
     */
    protected $_childrenIterator;

    /**
     * @param \Magento\Core\Model\App $application
     * @param \Magento\Backend\Model\Config\Structure\Element\Iterator $childrenIterator
     */
    public function __construct(
        \Magento\Core\Model\App $application,
        \Magento\Backend\Model\Config\Structure\Element\Iterator $childrenIterator
    ) {
        parent::__construct($application);
        $this->_childrenIterator = $childrenIterator;
    }

    /**
     * Set flyweight data
     *
     * @param array $data
     * @param string $scope
     */
    public function setData(array $data, $scope)
    {
        parent::setData($data, $scope);
        $children = array_key_exists('children', $this->_data) && is_array($this->_data['children']) ?
            $this->_data['children'] :
            array();
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
        };
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

