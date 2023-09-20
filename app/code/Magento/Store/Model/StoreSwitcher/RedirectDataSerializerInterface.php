<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data serializer interface
 *
 * @api
 */
interface RedirectDataSerializerInterface
{
    /**
     * Serialize provided data and return the serialized data
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string;

    /**
     * Unserialize provided data and return the unserialized data
     *
     * @param string $data
     * @return array
     */
    public function unserialize(string $data): array;
}
