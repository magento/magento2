<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\DtoProjection;

use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Perform DTO mapping
 */
class ProcessMapping
{
    /**
     * Run straight mapping
     *
     * @param array $source
     * @param array $mapping
     * @return array
     */
    public function execute(array $source, array $mapping): array
    {
        $out = [];

        foreach ($mapping as $to => $from) {
            $toPath = explode('.', $to);
            $fromPath = explode('.', $from);

            $fromValue = $this->getValue($source, $fromPath);
            $this->setValue($out, $toPath, $fromValue);
        }

        return $out;
    }

    /**
     * Set a value in a given path
     *
     * @param array $data
     * @param array $path
     * @param $value
     */
    private function setValue(array &$data, array $path, $value): void
    {
        $cursor = &$data;
        foreach ($path as $node) {
            $node = SimpleDataObjectConverter::camelCaseToSnakeCase($node);

            if (!isset($cursor[$node])) {
                $cursor[$node] = [];
            }
            $cursor = &$cursor[$node];
        }

        $cursor = $value;
        unset($cursor);
    }

    /**
     * Get a value in a given path of source data
     *
     * @param array $source
     * @param $path
     * @return mixed
     */
    private function getValue(array $source, $path)
    {
        $cursor = &$source;
        foreach ($path as $node) {
            $node = SimpleDataObjectConverter::camelCaseToSnakeCase($node);

            if (!isset($cursor[$node])) {
                $cursor = null;
                break;
            }

            $cursor = &$cursor[$node];
        }

        $res = $cursor;
        unset($cursor);

        return $res;
    }
}
