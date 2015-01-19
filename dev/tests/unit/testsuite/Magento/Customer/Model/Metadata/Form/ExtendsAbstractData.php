<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
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
