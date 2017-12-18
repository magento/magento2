<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * @TODO: factories instead of casters
 * Serves needs in integer digits. Default padding is 1.
 * Size is 1 byte.
 */
class Tinyinteger implements CasterInterface
{
    /**
     * Default padding number
     */
    const DEFAULT_PADDING = "1";

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
     * Set default padding, like TINYINT(1)
     *
     * {@inheritdoc}
     * @return array
     */
    public function cast(array $data)
    {
        $data = $this->base->cast($data);

        if (!isset($data['padding'])) {
            $data['padding'] = self::DEFAULT_PADDING;
        }

        if (isset($data['default'])) {
            $data['default'] = (int) $data['default'];
        }

        return $data;
    }
}
