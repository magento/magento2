<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;

/**
 * Serves foreign key constraint needs.
 * Add additonal onDelete param
 */
class Primary implements CasterInterface
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
     * Set default padding, like INTEGER(11)
     *
     * {@inheritdoc}
     * @return array
     */
    public function cast(array $data)
    {
        $data = $this->base->cast($data);
        $data['name'] = Internal::PRIMARY_NAME;

        return $data;
    }
}
