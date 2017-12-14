<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * Serves needs in integer digits. Default padding is 20.
 * Size is 4 byte.
 */
class Biginteger implements CasterInterface
{
    /**
     * Default padding number
     */
    const DEFAULT_PADDING = "20";

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
     * Set default padding, like BIGINT(20)
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
            $data['default'] = (int)$data['default'];
        }

        return $data;
    }
}
