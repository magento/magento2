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
namespace Magento\Bundle\Model;

class OptionTypeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\OptionTypeList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeBuilderMock;

    protected function setUp()
    {
        $this->typeMock = $this->getMock('\Magento\Bundle\Model\Source\Option\Type', [], [], '', false);
        $this->typeBuilderMock = $this->getMock(
            '\Magento\Bundle\Api\Data\OptionTypeDataBuilder',
            ['setCode', 'setLabel', 'create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Bundle\Model\OptionTypeList(
            $this->typeMock,
            $this->typeBuilderMock
        );
    }

    public function testGetItems()
    {
        $this->typeMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'value', 'label' => 'label']]);

        $typeMock = $this->getMock('\Magento\Bundle\Api\Data\OptionTypeInterface');
        $this->typeBuilderMock->expects($this->once())->method('setCode')->with('value')->willReturnSelf();
        $this->typeBuilderMock->expects($this->once())->method('setLabel')->with('label')->willReturnSelf();
        $this->typeBuilderMock->expects($this->once())->method('create')->willReturn($typeMock);
        $this->assertEquals([$typeMock], $this->model->getItems());
    }
}
