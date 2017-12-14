<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * Serves needs in integer digits. Default padding is 5.
 * Size is 2 bytes.
 */
class Smallinteger implements CasterInterface
{
    /**
     * Default small integer padding
     */
    const DEFAULT_PADDING = "6";

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
     * Set default padding, like SMALLINT(5)
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
