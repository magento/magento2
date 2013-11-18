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
 * @package     Magento_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Eav Form Fieldset Model
 *
 * @method \Magento\Eav\Model\Resource\Form\Fieldset getResource()
 * @method int getTypeId()
 * @method \Magento\Eav\Model\Form\Fieldset setTypeId(int $value)
 * @method string getCode()
 * @method \Magento\Eav\Model\Form\Fieldset setCode(string $value)
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Form\Fieldset setSortOrder(int $value)
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Form;

class Fieldset extends \Magento\Core\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_form_fieldset';

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Form\Fieldset');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Fieldset
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve resource collection instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Fieldset\Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Validate data before save data
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\Eav\Model\Form\Fieldset
     */
    protected function _beforeSave()
    {
        if (!$this->getTypeId()) {
            throw new \Magento\Core\Exception(__('Invalid form type.'));
        }
        if (!$this->getStoreId() && $this->getLabel()) {
            $this->setStoreLabel($this->getStoreId(), $this->getLabel());
        }

        return parent::_beforeSave();
    }

    /**
     * Retrieve fieldset labels for stores
     *
     * @return array
     */
    public function getLabels()
    {
        if (!$this->hasData('labels')) {
            $this->setData('labels', $this->_getResource()->getLabels($this));
        }
        return $this->_getData('labels');
    }

    /**
     * Set fieldset store labels
     * Input array where key - store_id and value = label
     *
     * @param array $labels
     * @return \Magento\Eav\Model\Form\Fieldset
     */
    public function setLabels(array $labels)
    {
        return $this->setData('labels', $labels);
    }

    /**
     * Set fieldset store label
     *
     * @param int $storeId
     * @param string $label
     * @return \Magento\Eav\Model\Form\Fieldset
     */
    public function setStoreLabel($storeId, $label)
    {
        $labels = $this->getLabels();
        $labels[$storeId] = $label;

        return $this->setLabels($labels);
    }

    /**
     * Retrieve label store scope
     *
     * @return int
     */
    public function getStoreId()
    {
        if (!$this->hasStoreId()) {
            $this->setData('store_id', $this->_storeManager->getStore()->getId());
        }
        return $this->_getData('store_id');
    }
}
