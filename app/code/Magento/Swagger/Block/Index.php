<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Swagger\Api\Data\SchemaTypeInterface;

/**
 * Block for swagger index page
 *
 * @api
 *
 * @method SchemaTypeInterface[] getSchemaTypes()
 * @method bool hasSchemaTypes()
 * @method string getDefaultSchemaTypeCode()
 */
class Index extends Template
{
    /**
     * @return mixed|string
     */
    private function getParamStore()
    {
        return $this->getRequest()->getParam('store') ?: 'all';
    }

    /**
     * @return SchemaTypeInterface|null
     */
    private function getSchemaType()
    {
        if (!$this->hasSchemaTypes()) {
            return null;
        }

        $schemaTypeCode = $this->getRequest()->getParam(
            'type',
            $this->getDefaultSchemaTypeCode()
        );

        if (!array_key_exists($schemaTypeCode, $this->getSchemaTypes())) {
            throw new \UnexpectedValueException(
                new Phrase('Unknown schema type supplied')
            );
        }

        return $this->getSchemaTypes()[$schemaTypeCode];
    }

    /**
     * @return string|null
     */
    public function getSchemaUrl()
    {
        if ($this->getSchemaType() === null) {
            return null;
        }

        return rtrim($this->getBaseUrl(), '/') .
            $this->getSchemaType()->getSchemaUrlPath($this->getParamStore());
    }
}
