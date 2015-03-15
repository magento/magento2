<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule5\Service\V2;

use Magento\TestModule5\Service\V2\Entity\AllSoapAndRest as AllSoapAndRestEntity;
use Magento\TestModule5\Service\V2\Entity\AllSoapAndRestFactory;

class AllSoapAndRest implements AllSoapAndRestInterface
{
    /**
     * @var AllSoapAndRestFactory
     */
    protected $factory;

    /**
     * @param AllSoapAndRestFactory $factory
     */
    public function __construct(AllSoapAndRestFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function item($id)
    {
        return $this->factory->create()->setPrice(1)->setId($id)->setName('testItemName');
    }

    /**
     * @inheritdoc
     */
    public function items()
    {
        $allSoapAndRest1 = $this->factory->create()->setPrice(1)->setId(1)->setName('testProduct1');
        $allSoapAndRest2 = $this->factory->create()->setPrice(1)->setId(2)->setName('testProduct2');
        return [$allSoapAndRest1, $allSoapAndRest2];
    }

    /**
     * @inheritdoc
     */
    public function create(\Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item)
    {
        return $this->factory->create()->setPrice($item->getPrice());
    }

    /**
     * @inheritdoc
     */
    public function update(\Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item)
    {
        $item->setName('Updated' . $item->getName());
        return $item;
    }

    /**
     * @param string $id
     * @return AllSoapAndRestEntity
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function delete($id)
    {
        return $this->factory->create()->setPrice(1)->setId($id)->setName('testItemName');
    }
}
