<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression\Field;

use Magento\Elasticsearch\Model\Lucene\Expression\AbstractExpression;

class Property extends AbstractExpression
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string[]
     */
    private $propertyPath;

    /**
     * @param string $fieldName
     * @param string[] $propertyPath
     */
    public function __construct(string $fieldName, array $propertyPath)
    {
        $this->fieldName = trim($fieldName);

        if (empty($this->fieldName)) {
            throw new \InvalidArgumentException('Field name can not be empty.');
        }

        $this->propertyPath = array_filter(
            array_map('trim', $propertyPath),
            function ($part) {
                return '' !== $part;
            }
        );

        if (empty($this->propertyPath)) {
            throw new \InvalidArgumentException('Property path can not be empty.');
        }
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return string[]
     */
    public function getPropertyPath(): array
    {
        return $this->propertyPath;
    }

    public function __toString(): string
    {
        return "doc['{$this->getFieldName()}']." . implode('.', $this->getPropertyPath());
    }
}
