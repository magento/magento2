<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Operation\Write;

use Magento\Framework\Model\Operation\WriteInterface;
use Magento\Framework\Model\Entity\Action\DeleteMain;
use Magento\Framework\Model\Entity\Action\DeleteExtension;
use Magento\Framework\Model\Entity\Action\DeleteRelation;

/**
 * Class Delete
 */
class Delete implements WriteInterface
{
    /**
     * @var DeleteMain
     */
    protected $deleteMain;

    /**
     * @var DeleteExtension
     */
    protected $deleteExtension;

    /**
     * @var DeleteRelation
     */
    protected $deleteRelation;

    /**
     * @param DeleteMain $deleteMain
     * @param DeleteExtension $deleteExtension
     * @param DeleteRelation $deleteRelation
     */
    public function __construct(
        DeleteMain $deleteMain,
        DeleteExtension $deleteExtension,
        DeleteRelation $deleteRelation
    ) {
        $this->deleteMain = $deleteMain;
        $this->deleteExtension = $deleteExtension;
        $this->deleteRelation = $deleteRelation;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return true
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $this->deleteRelation->execute($entityType, $entity);
        $this->deleteExtension->execute($entityType, $entity);
        $this->deleteMain->execute($entityType, $entity);
        return true;
    }
}
