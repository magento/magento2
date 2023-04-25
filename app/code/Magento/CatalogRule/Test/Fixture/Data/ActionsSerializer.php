<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Fixture\Data;

use Magento\CatalogRule\Model\Rule\Action\Collection;
use Magento\Framework\Serialize\Serializer\Json;

class ActionsSerializer
{
    /**
     * @var Json
     */
    private Json $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * Normalizes and serializes actions data
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string
    {
        return $this->json->serialize($this->normalize($data));
    }

    /**
     * Normalizes actions data
     *
     * @param array $data
     * @return array
     */
    private function normalize(array $data) : array
    {
        $actions = $data;
        $actions += [
            'type' => Collection::class,
            'attribute' => null,
            'value' => null,
            'operator' => '=',
        ];
        return $actions;
    }
}
