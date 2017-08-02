<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

use Ramsey\Uuid\Uuid;

/**
 * Class IdentityService
 * @since 2.2.0
 */
class IdentityService implements IdentityGeneratorInterface
{
    /**
     * @var \Ramsey\Uuid\UuidFactoryInterface
     * @since 2.2.0
     */
    private $uuidFactory;

    /**
     * IdentityService constructor.
     * @since 2.2.0
     */
    public function __construct()
    {
        $this->uuidFactory = new \Ramsey\Uuid\UuidFactory();
    }

    /**
     * @inheritDoc
     * @since 2.2.0
     */
    public function generateId()
    {
        $uuid = $this->uuidFactory->uuid4();
        return $uuid->toString();
    }

    /**
     * @inheritDoc
     * @since 2.2.0
     */
    public function generateIdForData($data)
    {
        $uuid = $this->uuidFactory->uuid3(Uuid::NAMESPACE_DNS, $data);
        return $uuid->toString();
    }
}
