<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\ViewModel;

use Magento\Framework\Serialize\Serializer\JsonHexTag;

class JsonSerializer implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var JsonHexTag
     */
    private $serializer;

    /**
     * @param JsonHexTag $serializer
     */
    public function __construct(JsonHexTag $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Returns serialized version of data
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string
    {
        return $this->serializer->serialize($data);
    }
}
