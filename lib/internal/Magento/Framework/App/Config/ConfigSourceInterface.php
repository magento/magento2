<?php
/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Interface ConfigSourceInterface
 */
interface ConfigSourceInterface
{
    /**
     * Retrieve configuration raw data array.
     *
     * @param string $path The configuration path (e.g. section_id/group_id/field_id)
     * @return string|array Array will be returned if you use part of path (e.g. scope/scope_code/section_id)
     * ```php
     * [
     *      'group1' =>
     *      [
     *          'field1' => 'value1',
     *          'field2' => 'value2'
     *      ],
     *      'group2' =>
     *      [
     *          'field1' => 'value3'
     *      ]
     * ]
     * ```
     * And string will be returned if you use full path to field (e.g. scope/scope_code/section_id/group_id/field_id)
     * E.g. 'value1'
     */
    public function get($path = '');
}
