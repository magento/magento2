<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Document;

/**
 * @api
 * @since 100.1.0
 */
class Builder
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @return array
     * @since 100.1.0
     */
    public function build()
    {
        $document = [];
        foreach ($this->fields as $field => $value) {
            $document = $this->addFieldToDocument($document, $field, $value);
        }
        $this->clear();
        return $document;
    }

    /**
     * @return void
     */
    private function clear()
    {
        $this->fields = [];
    }

    /**
     * @param array $document
     * @param string $field
     * @param string|int|float $value
     * @return array
     */
    private function addFieldToDocument($document, $field, $value)
    {
        if (is_array($value)) {
            if (count($value) == 0) {
                $document = array_merge($document, [$field => $value]);
            } else {
                $fields = [];
                foreach ($value as $key => $val) {
                    $fields[$field][$key] = $val;
                }
                $document = array_merge($document, $fields);
            }
        } else {
            $field = [$field => $value];
            $document = array_merge($document, $field);
        }
        return $document;
    }

    /**
     * @param string $field
     * @param string|array|int|float $value
     * @return $this
     * @since 100.1.0
     */
    public function addField($field, $value)
    {
        $this->fields[$field] = $value;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     * @since 100.1.0
     */
    public function addFields(array $fields)
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }
}
