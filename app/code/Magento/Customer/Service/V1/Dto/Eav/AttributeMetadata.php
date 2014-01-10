<?php
/**
 * Eav Attribute Metadata
 *
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
namespace Magento\Customer\Service\V1\Dto\Eav;

use Magento\Customer\Service\V1\Dto\Eav\Option;

class AttributeMetadata extends \Magento\Service\Entity\AbstractDto
{
    /**
     * Constants used as keys into $_data
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const FRONT_END_INPUT = 'front_end_input';
    const INPUT_FILTER = 'input_filter';
    const STORE_LABEL = 'store_label';
    const VALIDATION_RULES = 'validation_rules';
    const OPTIONS = 'options';
    const VISIBLE = 'is_visible';
    const REQUIRED = 'is_required';
    const MULTILINE_COUNT = 'multiline_count';
    const DATA_MODEL = 'data_model';

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * @return string
     */
    public function getFrontendInput()
    {
        return $this->_get(self::FRONT_END_INPUT);
    }

    /**
     * @return string
     */
    public function getInputFilter()
    {
        return $this->_get(self::INPUT_FILTER);
    }

    /**
     * @return string
     */
    public function getStoreLabel()
    {
        return $this->_get(self::STORE_LABEL);
    }

    /**
     * @return string
     */
    public function getValidationRules()
    {
        return $this->_get(self::VALIDATION_RULES);
    }

    /**
     * @return int
     */
    public function getMultilineCount()
    {
        return $this->_get(self::MULTILINE_COUNT);
    }

    /**
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->_get(self::VISIBLE);
    }

    /**
     * @return boolean
     */
    public function getIsRequired()
    {
        return $this->_get(self::REQUIRED);
    }

    /**
     * @return string
     */
    public function getDataModel()
    {
        return $this->_get(self::DATA_MODEL);
    }

    /**
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }
}
