<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule5\Service\V2;

use Magento\TestModule5\Service\V2\Entity\AllSoapAndRest as AllSoapAndRestEntity;
use Magento\TestModule5\Service\V2\Entity\AllSoapAndRestBuilder;

class AllSoapAndRest implements AllSoapAndRestInterface
{
    /**
     * @var AllSoapAndRestBuilder
     */
    protected $builder;

    /**
     * @param AllSoapAndRestBuilder $builder
     */
    public function __construct(AllSoapAndRestBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @inheritdoc
     */
    public function item($id)
    {
        return $this->builder->setPrice(1)->setId($id)->setName('testItemName')->create();
    }

    /**
     * @inheritdoc
     */
    public function items()
    {
        $allSoapAndRest1 = $this->builder->setPrice(1)->setId(1)->setName('testProduct1')->create();
        $allSoapAndRest2 = $this->builder->setPrice(1)->setId(2)->setName('testProduct2')->create();
        return [$allSoapAndRest1, $allSoapAndRest2];
    }

    /**
     * @inheritdoc
     */
    public function create(\Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item)
    {
        return $this->builder->populate($item)->create();
    }

    /**
     * @inheritdoc
     */
    public function update(\Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item)
    {
        $item->setName('Updated' . $item->getName());
        return $this->builder->populate($item)->create();
    }

    /**
     * @param string $id
     * @return AllSoapAndRestEntity
     * @throws \Magento\Webapi\Exception
     */
    public function delete($id)
    {
        return $this->builder->setPrice(1)->setId($id)->setName('testItemName')->create();
    }
}
