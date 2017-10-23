<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig\Hash;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Hash generator of config data.
 */
class Generator
{
    /**
     * Serializes data into string.
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer the serializer that serializes data into string
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
     */
    public function generate($data)
    {
        return sha1($this->serializer->serialize($data));
    }
}
