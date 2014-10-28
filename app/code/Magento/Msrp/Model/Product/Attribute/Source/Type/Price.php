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
namespace Magento\Msrp\Model\Product\Attribute\Source\Type;

/**
 * Source model for 'msrp_display_actual_price_type' product attribute
 */
class Price extends \Magento\Msrp\Model\Product\Attribute\Source\Type
{
    /**
     * Get value from the store configuration settings
     */
    const TYPE_USE_CONFIG = 0;

    /**
     * Entity attribute factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\AttributeFactory
     */
    protected $entityAttributeFactory;

    /**
     * Eav resource helper
     *
     * @var \Magento\Eav\Model\Resource\Helper
     */
    protected $eavResourceHelper;

    /**
     * Construct
     *
     * @param \Magento\Eav\Model\Resource\Entity\AttributeFactory $entityAttributeFactory
     * @param \Magento\Eav\Model\Resource\Helper $eavResourceHelper
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\AttributeFactory $entityAttributeFactory,
        \Magento\Eav\Model\Resource\Helper $eavResourceHelper
    ) {
        $this->entityAttributeFactory = $entityAttributeFactory;
        $this->eavResourceHelper = $eavResourceHelper;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array_merge(
                [['label' => __('Use config'), 'value' => self::TYPE_USE_CONFIG]],
                parent::getAllOptions()
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeType = $this->getAttribute()->getBackendType();
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => $this->eavResourceHelper->getDdlTypeByColumnType($attributeType),
                'nullable' => true,
            ],
        ];
    }

    /**
     * Retrieve select for flat attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->entityAttributeFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
