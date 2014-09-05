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
namespace Magento\Catalog\Service\V1\Data\Eav;

use Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class Attribute extends AbstractExtensibleObject
{
    const ID = 'id';
    const CODE = 'code';
    const IS_REQUIRED = 'is_required';
    const IS_USER_DEFINED = 'is_user_defined';
    const LABEL = 'frontend_label';
    const DEFAULT_VALUE = 'default_value';
    const FRONTEND_INPUT = 'frontend_input';

    /**
     * Get attribute ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Get attribute frontend label
     *
     * @return string|null
     */
    public function getFrontendLabel()
    {
        return $this->_get(self::LABEL);
    }

    /**
     * Get attribute default value
     *
     * @return string|null
     */
    public function getDefaultValue()
    {
        return $this->_get(self::DEFAULT_VALUE);
    }

    /**
     * Get attribute is_required flag
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRequired()
    {
        return $this->_get(self::IS_REQUIRED);
    }

    /**
     * Get attribute is_user_defined flag
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsUserDefined()
    {
        return $this->_get(self::IS_USER_DEFINED);
    }

    /**
     * Get frontend input type
     *
     * @return string
     */
    public function getFrontendInput()
    {
        return $this->_get(self::FRONTEND_INPUT);
    }
}
