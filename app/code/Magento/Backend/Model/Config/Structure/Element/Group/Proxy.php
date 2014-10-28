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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Structure\Element\Group;

class Proxy extends \Magento\Backend\Model\Config\Structure\Element\Group
{
    /**
     * Object manager
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Group
     */
    protected $_subject;

    /**
     * @param \Magento\Framework\ObjectManager $objectManger
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManger)
    {
        $this->_objectManager = $objectManger;
    }

    /**
     * Retrieve subject
     *
     * @return \Magento\Backend\Model\Config\Structure\Element\Group
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = $this->_objectManager->create('Magento\Backend\Model\Config\Structure\Element\Group');
        }
        return $this->_subject;
    }

    /**
     * Set element data
     *
     * @param array $data
     * @param string $scope
     * @return void
     */
    public function setData(array $data, $scope)
    {
        $this->_getSubject()->setData($data, $scope);
    }

    /**
     * Retrieve element id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_getSubject()->getId();
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_getSubject()->getLabel();
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_getSubject()->getComment();
    }

    /**
     * Retrieve frontend model class name
     *
     * @return string
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
     */
    public function getAttribute($key)
    {
        return $this->_getSubject()->getAttribute($key);
    }

    /**
     * Check whether section is allowed for current user
     *
     * @return bool
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
     */
    public function isVisible($websiteCode = '', $storeCode = '')
    {
        return $this->_getSubject()->isVisible($websiteCode, $storeCode);
    }

    /**
     * Retrieve css class of a tab
     *
     * @return string
     */
    public function getClass()
    {
        return $this->_getSubject()->getClass();
    }

    /**
     * Check whether element has visible child elements
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->_getSubject()->hasChildren();
    }

    /**
     * Retrieve children iterator
     *
     * @return \Magento\Backend\Model\Config\Structure\Element\Iterator
     */
    public function getChildren()
    {
        return $this->_getSubject()->getChildren();
    }

    /**
     * Should group fields be cloned
     *
     * @return bool
     */
    public function shouldCloneFields()
    {
        return $this->_getSubject()->shouldCloneFields();
    }

    /**
     * Retrieve clone model
     *
     * @return \Magento\Framework\Model\AbstractModel
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
     */
    public function populateFieldset(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $this->_getSubject()->populateFieldset($fieldset);
    }

    /**
     * Retrieve element data
     *
     * @return array
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
     */
    public function getPath($fieldPrefix = '')
    {
        return $this->_getSubject()->getPath($fieldPrefix);
    }

    /**
     * Check whether element should be expanded
     *
     * @return bool
     */
    public function isExpanded()
    {
        return $this->_getSubject()->isExpanded();
    }

    /**
     * Retrieve fieldset css
     *
     * @return string
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
     */
    public function getDependencies($storeCode)
    {
        return $this->_getSubject()->getDependencies($storeCode);
    }
}
