<?php
/**
 * Unit test for Magento\Framework\ValidatorFactory
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
namespace Magento\Framework;

use Magento\TestFramework\Helper\ObjectManager;

class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    /** @var \Magento\Framework\ObjectManager | \PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject('Magento\Framework\ValidatorFactory',
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreateWithInstanceName()
    {
        $setName = 'Magento\Framework\Object';
        $returnMock = $this->getMock($setName);
        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($returnMock);

        $this->assertSame($returnMock, $this->model->create());
    }

    public function testCreateDefault()
    {
        $default = 'Magento\Framework\Validator';
        $returnMock = $this->getMock($default);
        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($returnMock);
        $this->assertSame($returnMock, $this->model->create());
    }
}