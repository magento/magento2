<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule4\Service\V1\Entity;

interface ExtensibleRequestInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return int|null
     */
    public function getEntityId();

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);
}
