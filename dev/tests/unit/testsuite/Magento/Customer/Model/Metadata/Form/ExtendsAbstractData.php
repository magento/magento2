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
namespace Magento\Customer\Model\Metadata\Form;

/**
 * Class ExtendsAbstractData
 *
 * This test exists to aid with direct testing of the AbstractData class
 */
class ExtendsAbstractData extends AbstractData
{
    /**
     * {@inheritdoc}
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function restoreValue($value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
    }

    /**
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
    }

    /**
     * @return string
     */
    public function getRequestScope()
    {
        return $this->_requestScope;
    }

    /**
     * @return bool
     */
    public function isRequestScopeOnly()
    {
        return $this->_requestScopeOnly;
    }

    /**
     * @param string $value
     * @return bool|string
     */
    public function applyInputFilter($value)
    {
        return $this->_applyInputFilter($value);
    }

    /**
     * @param string|null|bool $format
     * @return AbstractData|string
     */
    public function dateFilterFormat($format)
    {
        return $this->_dateFilterFormat($format);
    }

    /**
     * @param string $value
     * @return string
     */
    public function applyOutputFilter($value)
    {
        return $this->_applyOutputFilter($value);
    }

    /**
     * @param string $value
     * @return bool|string
     */
    public function validateInputRule($value)
    {
        return $this->_validateInputRule($value);
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function getRequestValue(\Magento\Framework\App\RequestInterface $request)
    {
        return $this->_getRequestValue($request);
    }
}
