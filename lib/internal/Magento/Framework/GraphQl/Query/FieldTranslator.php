<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

/**
 * Translate field names to their database equivalent
 */
class FieldTranslator
{
    /**
     * @var string[]
     */
    private $translationMap = [];

    /**
     * @param string[] $translationMap
     */
    public function __construct(array $translationMap)
    {
        $this->translationMap = $translationMap;
    }

    /**
     * Return translated field name if present in configuration, otherwise return back the original passed in name.
     *
     * @param string $fieldName
     * @return string
     */
    public function translate(string $fieldName) : string
    {
        if (isset($this->translationMap[$fieldName])) {
            return $this->translationMap[$fieldName];
        }

        return $fieldName;
    }
}
