<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Api\DeleteWidgetInstanceByIdInterface;
use Magento\Widget\Model\ResourceModel\Widget\Instance as InstanceResourceModel;
use Magento\Widget\Model\Widget\InstanceFactory as WidgetInstanceFactory;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;

/**
 * Class DeleteWidgetInstanceById
 */
class DeleteWidgetInstanceById implements DeleteWidgetInstanceByIdInterface
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
     * @param int $id
     * @return void
     */
    public function execute(int $id)
    {
        $model = $this->getById($id);
        var_dump($model->getData(), $id);
        $this->resourceModel->delete($model);
    }

    /**
     * @param int $id
     * @return WidgetInstance
     * @throws NoSuchEntityException
     */
    private function getById(int $id)
    {
        $widgetInstance = $this->instanceFactory->create();

        $this->resourceModel->load($widgetInstance, $id);

        if (!$widgetInstance->getId()) {
            throw NoSuchEntityException::singleField('instance_id', $id);
        }

        return $widgetInstance;
    }
}
