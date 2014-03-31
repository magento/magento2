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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Quantity and Stock Status attribute processing
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;

class Stock extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Stock item factory
     *
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $_stockItemFactory;

    /**
     * Construct
     *
     * @param \Magento\Logger $logger
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory
    ) {
        $this->_stockItemFactory = $stockItemFactory;
        parent::__construct($logger);
    }

    /**
     * Set inventory data to custom attribute
     *
     * @param Product $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $item = $this->_stockItemFactory->create();
        $item->loadByProduct($object);
        $object->setData(
            $this->getAttribute()->getAttributeCode(),
            array('is_in_stock' => $item->getIsInStock(), 'qty' => $item->getQty())
        );
        return parent::afterLoad($object);
    }

    /**
     * Prepare inventory data from custom attribute
     *
     * @param Product $object
     * @return void
     */
    public function beforeSave($object)
    {
        $stockData = $object->getData($this->getAttribute()->getAttributeCode());
        if (isset($stockData['qty']) && $stockData['qty'] === '') {
            $stockData['qty'] = null;
        }
        if ($object->getStockData() !== null || $stockData !== null) {
            $object->setStockData(array_replace((array)$object->getStockData(), (array)$stockData));
        }
        $object->unsetData($this->getAttribute()->getAttributeCode());
        parent::beforeSave($object);
    }

    /**
     * Validate
     *
     * @param Product $object
     * @throws \Magento\Model\Exception
     * @return bool
     */
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!empty($value['qty']) && !preg_match('/^-?\d*(\.|,)?\d{0,4}$/i', $value['qty'])) {
            throw new \Magento\Model\Exception(__('Please enter a valid number in this field.'));
        }
        return true;
    }
}
