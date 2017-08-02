<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig\Hash;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Hash generator of config data.
 * @since 2.2.0
 */
class Generator
{
    /**
     * Serializes data into string.
     *
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer the serializer that serializes data into string
     * @since 2.2.0
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Generates and retrieves hash of deployment configuration data.
     *
     * @param array|string $data the deployment configuration data from files
     * @return string the hash
     * @since 2.2.0
     */
    public function generate($data)
    {
        return sha1($this->serializer->serialize($data));
    }
}
