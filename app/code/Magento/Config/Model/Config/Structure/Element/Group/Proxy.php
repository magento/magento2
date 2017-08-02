<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element\Group;

/**
 * @api
 * @since 2.0.0
 */
class Proxy extends \Magento\Config\Model\Config\Structure\Element\Group implements
    \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object manager
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Group
     * @since 2.0.0
     */
    protected $_subject;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManger
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManger)
    {
        $this->_objectManager = $objectManger;
    }

    /**
     * Retrieve subject
     *
     * @return \Magento\Config\Model\Config\Structure\Element\Group
     * @since 2.0.0
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = $this->_objectManager->create(
                \Magento\Config\Model\Config\Structure\Element\Group::class
            );
        }
        return $this->_subject;
    }

    /**
     * Set element data
     *
     * @param array $data
     * @param string $scope
     * @return void
     * @since 2.0.0
     */
    public function setData(array $data, $scope)
    {
        $this->_getSubject()->setData($data, $scope);
    }

    /**
     * Retrieve element id
     *
     * @return string
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->_getSubject()->getId();
    }

    /**
     * Retrieve element label
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel()
    {
        return $this->_getSubject()->getLabel();
    }

    /**
     * Retrieve element label
     *
     * @return string
     * @since 2.0.0
     */
    public function getComment()
    {
        return $this->_getSubject()->getComment();
    }

    /**
     * Retrieve frontend model class name
     *
     * @return string
     * @since 2.0.0
     */
    public function getFrontendModel()
    {
        return $this->_getSubject()->getFrontendModel();
    }

    /**
     * Retrieve arbitrary element attribute
     *
     * @param string $key
     * @return mixed
     * @since 2.0.0
     */
    public function getAttribute($key)
    {
        return $this->_getSubject()->getAttribute($key);
    }

    /**
     * Check whether section is allowed for current user
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAllowed()
    {
        return $this->_getSubject()->isAllowed();
    }

    /**
     * Check whether element should be displayed
     *
     * @param string $websiteCode
     * @param string $storeCode
     * @return bool
     * @since 2.0.0
     */
    public function isVisible($websiteCode = '', $storeCode = '')
    {
        return $this->_getSubject()->isVisible($websiteCode, $storeCode);
    }

    /**
     * Retrieve css class of a tab
     *
     * @return string
     * @since 2.0.0
     */
    public function getClass()
    {
        return $this->_getSubject()->getClass();
    }

    /**
     * Check whether element has visible child elements
     *
     * @return bool
     * @since 2.0.0
     */
    public function hasChildren()
    {
        return $this->_getSubject()->hasChildren();
    }

    /**
     * Retrieve children iterator
     *
     * @return \Magento\Config\Model\Config\Structure\Element\Iterator
     * @since 2.0.0
     */
    public function getChildren()
    {
        return $this->_getSubject()->getChildren();
    }

    /**
     * Should group fields be cloned
     *
     * @return bool
     * @since 2.0.0
     */
    public function shouldCloneFields()
    {
        return $this->_getSubject()->shouldCloneFields();
    }

    /**
     * Retrieve clone model
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    public function getCloneModel()
    {
        return $this->_getSubject()->getCloneModel();
    }

    /**
     * Populate form fieldset with group data
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return void
     * @since 2.0.0
     */
    public function populateFieldset(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $this->_getSubject()->populateFieldset($fieldset);
    }

    /**
     * Retrieve element data
     *
     * @return array
     * @since 2.0.0
     */
    public function getData()
    {
        return $this->_getSubject()->getData();
    }

    /**
     * Retrieve element path
     *
     * @param string $fieldPrefix
     * @return string
     * @since 2.0.0
     */
    public function getPath($fieldPrefix = '')
    {
        return $this->_getSubject()->getPath($fieldPrefix);
    }

    /**
     * Check whether element should be expanded
     *
     * @return bool
     * @since 2.0.0
     */
    public function isExpanded()
    {
        return $this->_getSubject()->isExpanded();
    }

    /**
     * Retrieve fieldset css
     *
     * @return string
     * @since 2.0.0
     */
    public function getFieldsetCss()
    {
        return $this->_getSubject()->getFieldsetCss();
    }

    /**
     * Retrieve element dependencies
     *
     * @param string $storeCode
     * @return array
     * @since 2.0.0
     */
    public function getDependencies($storeCode)
    {
        return $this->_getSubject()->getDependencies($storeCode);
    }
}
