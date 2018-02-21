<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export\Product\Type;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Export entity product type abstract model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractType
{
    /**
     * Overridden attributes parameters.
     *
     * @var array
     */
    protected $_attributeOverrides = [];

    /**
     * Array of attributes codes which are disabled for export.
     *
     * @var string[]
     */
    protected $_disabledAttrs = [];

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = [];

    /**
     * Return disabled attributes codes.
     *
     * @return string[]
     */
    public function getDisabledAttrs()
    {
        return $this->_disabledAttrs;
    }

    /**
     * Get attribute codes with index (not label) value.
     *
     * @return string[]
     */
    public function getIndexValueAttributes()
    {
        return $this->_indexValueAttributes;
    }

    /**
     * Additional check for model availability. If method returns FALSE - model is not suitable for data processing.
     *
     * @return bool
     */
    public function isSuitable()
    {
        return true;
    }

    /**
     * Add additional data to attribute.
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function overrideAttribute(Attribute $attribute)
    {
        if (!empty($this->_attributeOverrides[$attribute->getAttributeCode()])) {
            $data = $this->_attributeOverrides[$attribute->getAttributeCode()];

            if (isset($data['options_method']) && method_exists($this, $data['options_method'])) {
                $data['filter_options'] = $this->{$data['options_method']}();
            }
            $attribute->addData($data);

            return true;
        }
        return false;
    }
}
