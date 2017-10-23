<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

use Ramsey\Uuid\Uuid;

/**
 * Class IdentityService
 */
class IdentityService implements IdentityGeneratorInterface
{
    /**
     * @var \Ramsey\Uuid\UuidFactoryInterface
     */
    private $uuidFactory;

    /**
     * IdentityService constructor.
     */
    public function __construct()
    {
        $this->uuidFactory = new \Ramsey\Uuid\UuidFactory();
    }

    /**
     * @inheritDoc
     */
    public function generateId()
    {
        $uuid = $this->uuidFactory->uuid4();
        return $uuid->toString();
    }

    /**
     * @inheritDoc
     */
    public function generateIdForData($data)
    {
        $uuid = $this->uuidFactory->uuid3(Uuid::NAMESPACE_DNS, $data);
        return $uuid->toString();
    }
}
