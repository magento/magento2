<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * This type is equal to SQL DECIMAL(SCALE,PRECISION) type. Usually it is used for accurate operations
 * with decimal numbers. For example, for price
 * Usually decimal is concatinated from 2 integers, so it has not round problems
 */
class Decimal implements CasterInterface
{
    const DEFAULT_PRECISSION = "10";

    const DEFAULT_SCALE = "0";

    /**
     * @var Base
     */
    private $base;

    /**
     * @param Base $base
     */
    public function __construct(Base $base)
    {
        $this->base = $base;
    }

    /**
     * Set shape to floating point, that is by default (10,0)
     *
     * {@inheritdoc}
     * @return array
     */
    public function cast(array $data)
    {
        $data = $this->base->cast($data);

        if (!isset($data['precission'])) {
            $data['precission'] = self::DEFAULT_PRECISSION;
        }

        if (!isset($data['scale'])) {
            $data['scale'] = self::DEFAULT_SCALE;
        }

        if (isset($data['default'])) {
            $data['default'] = (float) $data['default'];
        }

        return $data;
    }
}
