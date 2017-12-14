<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Casters;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Can accept only 2 values: true or false
 */
class Boolean implements CasterInterface
{
    /**
     * Default value for boolean xsi:type
     */
    const DEFAULT_BOOLEAN = false;

    /**
     * @var Base
     */
    private $base;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param Base $base
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(Base $base, BooleanUtils $booleanUtils)
    {
        $this->base = $base;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Convert default attribute from string to boolean value
     *
     * {@inheritdoc}
     * @return array
     */
    public function cast(array $data)
    {
        $data = $this->base->cast($data);

        if (isset($data['default'])) {
            $data['default'] = $this->booleanUtils->toBoolean($data['default']);
        } else {
            $data['default'] = self::DEFAULT_BOOLEAN;
        }

        return $data;
    }
}
