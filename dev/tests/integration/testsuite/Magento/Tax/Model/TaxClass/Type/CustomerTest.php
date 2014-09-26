<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\TaxClass\Type;

use Magento\Customer\Service\V1\Data\CustomerGroupBuilder;

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
        $builder = $this->_objectManager->create('\Magento\Customer\Service\V1\Data\CustomerGroupBuilder');

        /* Create a tax class */
        $model = $this->_objectManager->create('Magento\Tax\Model\ClassModel');
        $model->setClassName("Test Group Tax Class")
            ->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->isObjectNew(true);
        $model->save();
        $taxClassId = $model->getId();

        $model->setId($taxClassId);
        /** @var $customerGroupService \Magento\Customer\Service\V1\CustomerGroupServiceInterface */
        $customerGroupService = $this->_objectManager->create('\Magento\Customer\Service\V1\CustomerGroupService');
        $group = $builder->setId(null)->setCode(self::GROUP_CODE)->setTaxClassId($taxClassId)
            ->create();
        $customerGroupService->createGroup($group);

        /** @var $model \Magento\Tax\Model\TaxClass\Type\Customer */
        $model = $this->_objectManager->create('Magento\Tax\Model\TaxClass\Type\Customer');
        $model->setId($taxClassId);
        $this->assertTrue($model->isAssignedToObjects());
    }
}

