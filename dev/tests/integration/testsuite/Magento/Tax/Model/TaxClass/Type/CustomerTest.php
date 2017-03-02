<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxClass\Type;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    const GROUP_CODE = 'Test Group';

    /**
     * @magentoDbIsolation enabled
     */
    public function testIsAssignedToObjects()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $groupFactory = $this->_objectManager->create(\Magento\Customer\Api\Data\GroupInterfaceFactory::class);

        /* Create a tax class */
        $model = $this->_objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $model->setClassName("Test Group Tax Class")
            ->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->isObjectNew(true);
        $model->save();
        $taxClassId = $model->getId();

        $model->setId($taxClassId);
        /** @var $groupRepository \Magento\Customer\Api\GroupRepositoryInterface */
        $groupRepository = $this->_objectManager->create(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $group = $groupFactory->create()->setId(null)->setCode(self::GROUP_CODE)->setTaxClassId($taxClassId);
        $groupRepository->save($group);

        /** @var $model \Magento\Tax\Model\TaxClass\Type\Customer */
        $model = $this->_objectManager->create(\Magento\Tax\Model\TaxClass\Type\Customer::class);
        $model->setId($taxClassId);
        $this->assertTrue($model->isAssignedToObjects());
    }
}
