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
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Bundle Price View Attribute Renderer
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Model\Product\Attribute\Source\Price;

class View
    extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory
     */
    protected $_entityAttribute;

    /**
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $entityAttribute
     * @param \Magento\Core\Helper\Data $coreData
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $entityAttribute,
        \Magento\Core\Helper\Data $coreData
    ) {
        $this->_coreData = $coreData;
        $this->_entityAttribute = $entityAttribute;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => __('As Low as'),
                    'value' =>  1
                ),
                array(
                    'label' => __('Price Range'),
                    'value' =>  0
                ),
            );
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned'  => false,
            'default'   => null,
            'extra'     => null
        );

        $column['type']     = \Magento\DB\Ddl\Table::TYPE_INTEGER;
        $column['nullable'] = true;
        $column['comment']  = 'Bundle Price View ' . $attributeCode . ' column';

        return array($attributeCode => $column);
   }

    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_entityAttribute->create()
            ->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}
