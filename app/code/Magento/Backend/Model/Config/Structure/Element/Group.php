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
namespace Magento\Backend\Model\Config\Structure\Element;

class Group extends AbstractComposite
{
    /**
     * Group clone model factory
     *
     * @var \Magento\Backend\Model\Config\BackendClone\Factory
     */
    protected $_cloneModelFactory;

    /**
     *
     * @var \Magento\Backend\Model\Config\Structure\Element\Dependency\Mapper
     */
    protected $_dependencyMapper;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Config\Structure\Element\Iterator\Field $childrenIterator
     * @param \Magento\Backend\Model\Config\BackendClone\Factory $cloneModelFactory
     * @param \Magento\Backend\Model\Config\Structure\Element\Dependency\Mapper $dependencyMapper
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Config\Structure\Element\Iterator\Field $childrenIterator,
        \Magento\Backend\Model\Config\BackendClone\Factory $cloneModelFactory,
        \Magento\Backend\Model\Config\Structure\Element\Dependency\Mapper $dependencyMapper
    ) {
        parent::__construct($storeManager, $childrenIterator);
        $this->_cloneModelFactory = $cloneModelFactory;
        $this->_dependencyMapper = $dependencyMapper;
    }

    /**
     * Should group fields be cloned
     *
     * @return bool
     */
    public function shouldCloneFields()
    {
        return isset($this->_data['clone_fields']) && !empty($this->_data['clone_fields']);
    }

    /**
     * Retrieve clone model
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Model\Exception
     */
    public function getCloneModel()
    {
        if (!isset($this->_data['clone_model']) || !$this->_data['clone_model']) {
            throw new \Magento\Framework\Model\Exception('Config form fieldset clone model required to be able to clone fields');
        }
        return $this->_cloneModelFactory->create($this->_data['clone_model']);
    }

    /**
     * Populate form fieldset with group data
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return void
     */
    public function populateFieldset(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $originalData = array();
        foreach ($this->_data as $key => $value) {
            if (!is_array($value)) {
                $originalData[$key] = $value;
            }
        }
        $fieldset->setOriginalData($originalData);
    }

    /**
     * Check whether group should be expanded
     *
     * @return bool
     */
    public function isExpanded()
    {
        return (bool)(isset($this->_data['expanded']) ? (int)$this->_data['expanded'] : false);
    }

    /**
     * Retrieve group fieldset css
     *
     * @return string
     */
    public function getFieldsetCss()
    {
        return array_key_exists('fieldset_css', $this->_data) ? $this->_data['fieldset_css'] : '';
    }

    /**
     * Retrieve field dependencies
     *
     * @param string $storeCode
     * @return array
     */
    public function getDependencies($storeCode)
    {
        $dependencies = array();
        if (false == isset($this->_data['depends']['fields'])) {
            return $dependencies;
        }

        $dependencies = $this->_dependencyMapper->getDependencies($this->_data['depends']['fields'], $storeCode);
        return $dependencies;
    }
}
