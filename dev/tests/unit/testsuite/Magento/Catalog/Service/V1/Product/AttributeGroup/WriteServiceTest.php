<?php
/**
 *
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
namespace Magento\Catalog\Service\V1\Product\AttributeGroup;

use Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupFactory;

    /**
     * @var WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $group;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder
     */
    protected $groupBuilder;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectHelper;

    protected function setUp()
    {
        $this->objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->groupFactory = $this->getMock(
            '\Magento\Catalog\Model\Product\Attribute\GroupFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->group = $this->getMock(
            '\Magento\Catalog\Model\Product\Attribute\Group',
            array(
                'getId', 'setId', 'setAttributeGroupName', '__wakeUp', 'save', 'load', 'delete', 'hasSystemAttributes'
            ),
            array(),
            '',
            false
        );
        $this->groupFactory->expects($this->any())->method('create')->will($this->returnValue($this->group));
        $this->groupBuilder = $this->objectHelper->getObject(
            'Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder'
        );
        $this->service = new WriteService($this->groupFactory, $this->groupBuilder);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateThrowsException()
    {
        $this->group->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $groupDataBuilder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder');
        $groupDataBuilder->setName('testName');
        $this->service->create(1, $groupDataBuilder->create());
    }

    public function testCreateCreatesNewAttributeGroup()
    {
        $this->group->expects($this->once())->method('setAttributeGroupName')->with('testName');
        $this->group->expects($this->once())->method('save');
        $groupDataBuilder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder');
        $groupDataBuilder->setName('testName');
        $this->service->create(1, $groupDataBuilder->create());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateThrowsExceptionIfNoSuchEntityExists()
    {
        $groupDataBuilder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder');
        $groupDataBuilder->setName('testName');
        $this->service->update(1, $groupDataBuilder->create());
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testUpdateThrowsExceptionIfEntityWasNotSaved()
    {
        $this->group->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $groupDataBuilder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder');
        $groupDataBuilder->setName('testName');
        $this->service->update(1, $groupDataBuilder->create());
    }

    public function testUpdateSavesEntity()
    {
        $this->group->expects($this->once())->method('save');
        $this->group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->group->expects($this->once())->method('setId')->with(null);
        $this->group->expects($this->once())->method('setAttributeGroupName')->with('testName');
        $groupDataBuilder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder');
        $groupDataBuilder->setName('testName');
        $this->service->update(1, $groupDataBuilder->create());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteThrowsExceptionIfNoEntityExists()
    {
        $this->group->expects($this->once())->method('getId')->will($this->returnValue(null));
        $groupDataBuilder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder');
        $groupDataBuilder->setName('testName');
        $this->service->delete(1, $groupDataBuilder->create());
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testDeleteThrowsStateExceptionIfTryToDeleteGroupWithSystemAttributes()
    {
        $this->group->expects($this->once())->method('hasSystemAttributes')->will($this->returnValue(true));
        $this->group->expects($this->never())->method('delete');
        $this->service->delete(1);
    }

    public function testDeleteRemovesEntity()
    {
        $this->group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->group->expects($this->once())->method('delete');
        $this->service->delete(1);
    }
}
