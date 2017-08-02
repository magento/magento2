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
 * @since 2.0.0
 */
class RelationComposite
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $relationProcessors;

    /**
     * @var EventManager
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @param EventManager $eventManager
     * @param array $relationProcessors
     * @since 2.0.0
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
     * @since 2.0.0
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
