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
namespace Magento\CatalogImportExport\Model\Export\Product\Type;

use Magento\Catalog\Model\Resource\Eav\Attribute;

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
    protected $_attributeOverrides = array();

    /**
     * Array of attributes codes which are disabled for export.
     *
     * @var string[]
     */
    protected $_disabledAttrs = array();

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = array();

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
