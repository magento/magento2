<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * Caster composite - holds all other casters
 */
class CasterAggregator implements CasterInterface
{
    /**
     * This caster always will exists in casters collection
     */
    const DEFAULT_CASTER = 'base';

    /**
     * @var array|CasterInterface[]
     */
    private $casters;

    /**
     * @param CasterInterface[] $casters
     */
    public function __construct(array $casters)
    {
        $this->casters = $casters;
    }

    /**
     * Find appropriate caster by type (for example: integer or decimal)
     * and try to cast it
     *
     * @inheritdoc
     */
    public function cast(array $data)
    {
        $type = $data['type'];

        if (!isset($this->casters[$type])) {
            //We can`t through any exception, as from db schema we can faced with unknown types
            $caster = $this->casters[self::DEFAULT_CASTER];
        } else {
            $caster = $this->casters[$type];
        }

        return $caster->cast($data);
    }
}
