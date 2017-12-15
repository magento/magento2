<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * Date caster
 * Remove default and nullable attributes, as date type must not have any attributes
 */
class Date implements CasterInterface
{
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
        unset($data['nullable']);
        unset($data['default']);
        return $data;
    }
}
