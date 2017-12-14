<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * This format is used to save date (year, month, day).
 * Probably your SQL engine will save date in this format: 'YYYY-MM-DD HH:MM::SS'
 * Date time in invalid format will be converted to '0000-00-00 00:00:00' string
 * MySQL timestamp is similar to UNIX timestamp. You can pass you local time there and it will
 * be converted to UTC timezone. Then when you will try to pull your time back it will be converted
 * to your local time again.
 * Unix range: 1970-01-01 00:00:01' UTC to '2038-01-09 03:14:07'
 */
class Timestamp implements CasterInterface
{
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
     * Change on_update and default params
     *
     * {@inheritdoc}
     * @return array
     */
    public function cast(array $data)
    {
        $data = $this->base->cast($data);
        //As we have only one value for timestamp on update -> it is convinient to use boolean type for it
        //But later we need to convert it to SQL value
        if (isset($data['on_update']) && $data['on_update'] !== 'CURRENT_TIMESTAMP') {
            if ($this->booleanUtils->toBoolean($data['on_update'])) {
                $data['on_update'] = 'CURRENT_TIMESTAMP';
            } else {
                unset($data['on_update']);
            }
        }
        //By default we do not want to use default attribute
        if (!isset($data['default'])) {
            $data['default'] = null;
        }

        unset($data['nullable']);

        return $data;
    }
}
