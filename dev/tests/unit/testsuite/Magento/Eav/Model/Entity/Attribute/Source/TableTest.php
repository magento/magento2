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
namespace Magento\Eav\Model\Entity\Attribute\Source;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\Table
     */
    protected $_model;

    /**
     * @var CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->collectionFactory = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory',
            [
                'create',
                'setPositionOrder',
                'setAttributeFilter',
                'addFieldToFilter',
                'setStoreFilter',
                'load',
                'toOptionArray'
            ],
            [],
            '',
            false
        );

        $this->_model = $objectManager->getObject(
            'Magento\Eav\Model\Entity\Attribute\Source\Table',
            ['attrOptionCollectionFactory' => $this->collectionFactory]
        );
    }

    public function testGetFlatColumns()
    {
        $abstractFrontendMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend',
            array(),
            array(),
            '',
            false
        );

        $abstractAttributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array('getFrontend', 'getAttributeCode', '__wakeup'),
            array(),
            '',
            false
        );

        $abstractAttributeMock->expects(
            $this->any()
        )->method(
            'getFrontend'
        )->will(
            $this->returnValue($abstractFrontendMock)
        );

        $abstractAttributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('code'));

        $this->_model->setAttribute($abstractAttributeMock);

        $flatColumns = $this->_model->getFlatColumns();

        $this->assertTrue(is_array($flatColumns), 'FlatColumns must be an array value');
        $this->assertTrue(!empty($flatColumns), 'FlatColumns must be not empty');

        foreach ($flatColumns as $result) {
            $this->assertArrayHasKey('unsigned', $result, 'FlatColumns must have "unsigned" column');
            $this->assertArrayHasKey('default', $result, 'FlatColumns must have "default" column');
            $this->assertArrayHasKey('extra', $result, 'FlatColumns must have "extra" column');
            $this->assertArrayHasKey('type', $result, 'FlatColumns must have "type" column');
            $this->assertArrayHasKey('nullable', $result, 'FlatColumns must have "nullable" column');
            $this->assertArrayHasKey('comment', $result, 'FlatColumns must have "comment" column');
            $this->assertArrayHasKey('length', $result, 'FlatColumns must have "length" column');
        }
    }

    /**
     * @dataProvider specificOptionsProvider
     * @param array $optionIds
     * @param bool $withEmpty
     */
    public function testGetSpecificOptions($optionIds, $withEmpty)
    {
        $attributeId = 1;
        $storeId = 5;
        $options = [['label' => 'The label', 'value' => 'A value']];

        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getId', 'getStoreId', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $attribute->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->_model->setAttribute($attribute);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setAttributeFilter')
            ->with($attributeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.option_id', ['in' => $optionIds])
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        if ($withEmpty) {
            array_unshift($options, ['label' => '', 'value' => '']);
        }

        $this->assertEquals($options, $this->_model->getSpecificOptions($optionIds, $withEmpty));

    }

    public function specificOptionsProvider()
    {
        return [
            [['1', '2'], true],
            [[1, 2], false]
        ];
    }

    /**
     * @dataProvider getOptionTextProvider
     * @param array $optionsIds
     * @param array|string $value
     * @param array $options
     * @param array|string $expectedResult
     */
    public function testGetOptionText($optionsIds, $value, $options, $expectedResult)
    {
        $attributeId = 1;
        $storeId = 5;
        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getId', 'getStoreId', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $attribute->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->_model->setAttribute($attribute);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setAttributeFilter')
            ->with($attributeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.option_id', ['in' => $optionsIds])
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        $this->assertEquals($expectedResult, $this->_model->getOptionText($value));
    }

    public function getOptionTextProvider()
    {
        return [
            [
                ['1', '2'],
                '1,2',
                [['label' => 'test label 1', 'value' => '1'], ['label' => 'test label 2', 'value' => '1']],
                ['test label 1', 'test label 2']
            ],
            ['1', '1', [['label' => 'test label', 'value' => '1']], 'test label'],
            ['5', '5', [['label' => 'test label', 'value' => '5']], 'test label']
        ];
    }
}
