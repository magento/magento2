<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Block;

use Magento\Framework\View\Element\Template;
use Magento\Swagger\Api\SchemaTypeInterface;

/**
 * Block for swagger index page
 *
 * @api
 *
 * @method \Magento\Swagger\Api\Block\SchemaTypesInterface getSchemaTypes()
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
     * @return SchemaTypeInterface
     */
    private function getParamSchemaType()
    {
        $schemaTypeCode = $this->getRequest()->getParam(
            'type',
            $this->getSchemaTypes()->getDefault()->getCode()
        );

        foreach ($this->getSchemaTypes()->getTypes() as $schemaType) {
            if ($schemaTypeCode === $schemaType->getCode()) {
                return $schemaType;
            }
        }

        return $this->getSchemaTypes()->getDefault();
    }

    /**
     * @return string
     */
    public function getSchemaUrl()
    {
        return rtrim($this->getBaseUrl(), '/') .
            $this->getParamSchemaType()->getSchemaUrlPath($this->getParamStore());
    }
}
