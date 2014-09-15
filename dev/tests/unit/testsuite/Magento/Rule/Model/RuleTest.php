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

namespace Magento\Rule\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Rule
     */
    protected $rule;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $combineFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $this->registryMock = $this->getMock('Magento\Framework\Registry');
        $this->formFactoryMock = $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false);
        $this->timezoneMock = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->combineFactoryMock = $this->getMock(
            'Magento\Rule\Model\Condition\CombineFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            'Magento\Rule\Model\Action\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rule = $this->objectManagerHelper->getObject(
            'Magento\Rule\Model\Rule',
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
                'localeDate' => $this->timezoneMock,
                'conditionsFactory' => $this->combineFactoryMock,
                'actionsFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testGetConditionsInstance()
    {
        $this->combineFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue(
                $this->getMock('\Magento\Rule\Model\Condition\Combine', [], [], '', false)
            ));
        $this->assertInstanceOf('\Magento\Rule\Model\Condition\Combine', $this->rule->getConditionsInstance());
    }

    public function testGetActionsInstance()
    {
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue(
                $this->getMock('\Magento\Rule\Model\Action\Collection', [], [], '', false)
            ));
        $this->assertInstanceOf('\Magento\Rule\Model\Action\Collection', $this->rule->getActionsInstance());
    }
}
