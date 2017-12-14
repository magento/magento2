<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Basic interpreter - used to map one xsi:type to element type
 */
class Base implements CasterInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * Base constructor.
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function cast(array $data)
    {
        $data['nullable'] = isset($data['nullable']) ? $this->booleanUtils->toBoolean($data['nullable']) : true;
        $data['unsigned'] = isset($data['unsigned']) ? $this->booleanUtils->toBoolean($data['unsigned']) : false;
        $data['disabled'] = isset($data['disabled']) ? $this->booleanUtils->toBoolean($data['disabled']) : false;

        if (isset($data['identity'])) {
            $data['identity'] = $this->booleanUtils->toBoolean($data['identity']);
        }

        unset($data['extra']);//we need to ignore extra field that comes from db

        return $data;
    }
}
