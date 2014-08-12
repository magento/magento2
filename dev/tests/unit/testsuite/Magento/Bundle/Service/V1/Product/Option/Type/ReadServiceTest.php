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
namespace Magento\Bundle\Service\V1\Product\Option\Type;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Bundle\Service\V1\Data\Product\Option\Type;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Service\V1\Product\Option\Type\ReadService
     */
    private $model;

    /**
     * @var \Magento\Bundle\Model\Source\Option\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeModel;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Option\TypeConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeConverter;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Option\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $type;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->typeModel = $this->getMockBuilder('Magento\Bundle\Model\Source\Option\Type')
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeConverter = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Option\TypeConverter')
            ->setMethods(['createDataFromModel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Option\Type')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            'Magento\Bundle\Service\V1\Product\Option\Type\ReadService',
            ['type' => $this->typeModel, 'typeConverter' => $this->typeConverter]
        );
    }

    public function testGetTypes()
    {
        $label = 'someLabel';
        $value = 'someValue';
        $this->typeModel->expects($this->once())->method('toOptionArray')
            ->will($this->returnValue([['label' => $label, 'value' => $value]]));

        $this->typeConverter->expects($this->once())->method('createDataFromModel')
            ->will($this->returnValue($this->type));

        $this->assertEquals([$this->type], $this->model->getTypes());
    }
}
