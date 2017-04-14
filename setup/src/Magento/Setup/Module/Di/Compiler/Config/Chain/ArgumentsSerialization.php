<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Used for argument's array serialization and store to the DI configuration.
 *
 * @deprecated Di arguments are now stored in raw php format and could be cached by OPcache,
 *             this class will be removed in the next backward incompatible release.
 */
class ArgumentsSerialization implements ModificationInterface
{
    /**
     * Used for serialize/unserialize data.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param SerializerInterface|null $serializer
     */
    public function __construct(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config)
    {
        if (!isset($config['arguments'])) {
            return $config;
        }

        foreach ($config['arguments'] as $key => $value) {
            if ($value !== null) {
                $config['arguments'][$key] = $this->serializer->serialize($value);
            }
        }

        return $config;
    }
}
