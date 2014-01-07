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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales abstract resource model
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Resource;

abstract class AbstractResource extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Data converter object
     *
     * @var \Magento\Sales\Model\ConverterInterface
     */
    protected $_converter = null;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Stdlib\DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Prepare data for save
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return array
     */
    protected function _prepareDataForSave(\Magento\Core\Model\AbstractModel $object)
    {
        $currentTime = $this->dateTime->now();
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }
        $object->setUpdatedAt($currentTime);
        $data = parent::_prepareDataForSave($object);
        return $data;
    }

    /**
     * Check if current model data should be converted
     *
     * @return bool
     */
    protected function _shouldBeConverted()
    {
        return (null !== $this->_converter);
    }


    /**
     * Perform actions before object save
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Sales\Model\Resource\AbstractResource
     */
    protected function _beforeSave(\Magento\Core\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);

        if (true == $this->_shouldBeConverted()) {
            foreach($object->getData() as $fieldName => $fieldValue) {
                $object->setData($fieldName, $this->_converter->encode($object, $fieldName));
            }
        }
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Sales\Model\Resource\AbstractResource
     */
    protected function _afterSave(\Magento\Core\Model\AbstractModel $object)
    {
        if (true == $this->_shouldBeConverted()) {
            foreach($object->getData() as $fieldName => $fieldValue) {
                $object->setData($fieldName, $this->_converter->decode($object, $fieldName));
            }
        }
        return parent::_afterSave($object);
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Sales\Model\Resource\AbstractResource
     */
    protected function _afterLoad(\Magento\Core\Model\AbstractModel $object)
    {
        if (true == $this->_shouldBeConverted()) {
            foreach($object->getData() as $fieldName => $fieldValue) {
                $object->setData($fieldName, $this->_converter->decode($object, $fieldName));
            }
        }
        return parent::_afterLoad($object);
    }
}
