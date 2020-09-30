<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Customer\Model\Metadata\Form\AbstractData;
use Magento\Framework\App\RequestInterface;

/**
 *
 * This test exists to aid with direct testing of the AbstractData class
 */
class ExtendsAbstractData extends AbstractData
{
    /**
     * {@inheritdoc}
     */
    public function extractValue(RequestInterface $request)
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
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
    {
    }

    /**
     * @param AttributeMetadataInterface $attribute
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
     * @param RequestInterface $request
     * @return mixed
     */
    public function getRequestValue(RequestInterface $request)
    {
        return $this->_getRequestValue($request);
    }
}
