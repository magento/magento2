<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\ResourceModel\Widget\Instance as InstanceResourceModel;
use Magento\Widget\Model\Widget\InstanceFactory as WidgetInstanceFactory;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;

/**
 * Class DeleteWidgetById
 */
class DeleteWidgetById
{
    /**
     * @var InstanceResourceModel
     */
    private $resourceModel;

    /**
     * @var WidgetInstanceFactory|WidgetInstance
     */
    private $instanceFactory;

    /**
     * @param InstanceResourceModel $resourceModel
     * @param WidgetInstanceFactory $instanceFactory
     */
    public function __construct(
        InstanceResourceModel $resourceModel,
        WidgetInstanceFactory $instanceFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->instanceFactory = $instanceFactory;
    }

    /**
     * Delete widget instance by given instance ID
     *
     * @param int $instanceId
     * @return void
     * @throws \Exception
     */
    public function execute(int $instanceId) : void
    {
        $model = $this->getWidgetById($instanceId);

        $this->resourceModel->delete($model);
    }

    /**
     * Get widget instance by given instance ID
     *
     * @param int $instanceId
     * @return WidgetInstance
     * @throws NoSuchEntityException
     */
    private function getWidgetById(int $instanceId): WidgetInstance
    {
        /** @var WidgetInstance $widgetInstance */
        $widgetInstance = $this->instanceFactory->create();

        $this->resourceModel->load($widgetInstance, $instanceId);

        if (!$widgetInstance->getId()) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with instance_id = %instance_id',
                    ['instance_id' => $instanceId]
                )
            );
        }

        return $widgetInstance;
    }
}
