<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\VersionControl;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class RelationComposite
 */
class RelationComposite
{
    /**
     * @var array
     */
    protected $relationProcessors;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @param EventManager $eventManager
     * @param array $relationProcessors
     */
    public function __construct(
        EventManager $eventManager,
        array $relationProcessors = []
    ) {
        $this->eventManager = $eventManager;
        $this->relationProcessors = $relationProcessors;
    }

    /**
     * Process model's relations saves
     *
     * @param AbstractModel $object
     * @return void
     */
    public function processRelations(AbstractModel $object)
    {
        foreach ($this->relationProcessors as $processor) {
            /**@var $processor RelationInterface*/
            $processor->processRelation($object);
        }
        $this->eventManager->dispatch(
            $object->getEventPrefix(). '_process_relation',
            [
                'object' => $object
            ]
        );
    }
}
