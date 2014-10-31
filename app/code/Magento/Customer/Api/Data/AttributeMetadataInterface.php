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

namespace Magento\Customer\Api\Data;

/**
 * Customer attribute metadata interface.
 */
interface AttributeMetadataInterface
{
    /**
     * Retrieve code of the attribute.
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Frontend HTML for input element.
     *
     * @return string
     */
    public function getFrontendInput();

    /**
     * Get template used for input (e.g. "date")
     *
     * @return string
     */
    public function getInputFilter();

    /**
     * Get label of the store.
     *
     * @return string
     */
    public function getStoreLabel();

    /**
     * Retrieve validation rules.
     *
     * @return \Magento\Customer\Service\V1\Data\Eav\ValidationRule[]
     */
    public function getValidationRules();

    /**
     * Number of lines of the attribute value.
     *
     * @return int
     */
    public function getMultilineCount();

    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool
     */
    public function isVisible();

    /**
     * Whether attribute is required.
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Get data model for attribute.
     *
     * @return string
     */
    public function getDataModel();

    /**
     * Return options of the attribute (key => value pairs for select)
     *
     * @return \Magento\Customer\Service\V1\Data\Eav\Option[]
     */
    public function getOptions();

    /**
     * Get class which is used to display the attribute on frontend.
     *
     * @return string
     */
    public function getFrontendClass();

    /**
     * Whether current attribute has been defined by a user.
     *
     * @return bool
     */
    public function isUserDefined();

    /**
     * Get attributes sort order.
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get label which supposed to be displayed on frontend.
     *
     * @return string
     */
    public function getFrontendLabel();

    /**
     * Get the note attribute for the element.
     *
     * @return string
     */
    public function getNote();

    /**
     * Whether this is a system attribute.
     *
     * @return bool
     */
    public function isSystem();

    /**
     * Get backend type.
     *
     * @return string
     */
    public function getBackendType();
}
