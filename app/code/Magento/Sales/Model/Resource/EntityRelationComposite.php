<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Resource;

use Magento\Sales\Model\AbstractModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class EntityRelationComposite
 */
class EntityRelationComposite
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
     * @param AbstractModel $object
     * @return void
     */
    public function processRelations(AbstractModel $object)
    {
        foreach ($this->relationProcessors as $processor) {
            /**@var $processor \Magento\Sales\Model\Resource\EntityRelationInterface*/
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
