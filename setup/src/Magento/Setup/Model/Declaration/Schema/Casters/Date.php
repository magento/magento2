<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * Date caster
 * Should not have any values
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
