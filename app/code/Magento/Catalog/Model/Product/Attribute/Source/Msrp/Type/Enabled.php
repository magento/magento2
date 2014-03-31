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
 * Source model for 'msrp_enabled' product attribute
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Source\Msrp\Type;

class Enabled extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Enable MAP
     */
    const MSRP_ENABLE_YES = 1;

    /**
     * Disable MAP
     */
    const MSRP_ENABLE_NO = 0;

    /**
     * Get value from the store configuration settings
     */
    const MSRP_ENABLE_USE_CONFIG = 2;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Entity attribute factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\AttributeFactory
     */
    protected $_entityAttributeFactory;

    /**
     * Construct
     *
     * @param \Magento\Eav\Model\Resource\Entity\AttributeFactory $entityAttributeFactory
     * @param \Magento\Core\Helper\Data $coreData
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\AttributeFactory $entityAttributeFactory,
        \Magento\Core\Helper\Data $coreData
    ) {
        $this->_entityAttributeFactory = $entityAttributeFactory;
        $this->_coreData = $coreData;
    }

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array('label' => __('Yes'), 'value' => self::MSRP_ENABLE_YES),
                array('label' => __('No'), 'value' => self::MSRP_ENABLE_NO),
                array('label' => __('Use config'), 'value' => self::MSRP_ENABLE_USE_CONFIG)
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array('unsigned' => false, 'default' => null, 'extra' => null);

        $column['type'] = \Magento\DB\Ddl\Table::TYPE_SMALLINT;
        $column['length'] = 1;
        $column['nullable'] = true;
        $column['comment'] = $attributeCode . ' column';

        return array($attributeCode => $column);
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_entityAttributeFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
