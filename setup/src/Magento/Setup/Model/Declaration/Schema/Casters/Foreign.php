<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * Serves foreign key constraint needs.
 * Add additonal onDelete param
 */
class Foreign implements CasterInterface
{
    /** Default padding number */
    const DEFAULT_ON_DELETE = "CASCADE";

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
     * Set default padding, like INTEGER(11)
     *
     * {@inheritdoc}
     * @return array
     */
    public function cast(array $data)
    {
        $data = $this->base->cast($data);

        if (!isset($data['onDelete'])) {
            $data['onDelete'] = self::DEFAULT_ON_DELETE;
        }

        return $data;
    }
}
