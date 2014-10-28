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

namespace Magento\Rule\Model\Renderer;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ConditionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Renderer\Conditions
     */
    protected $conditions;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_element;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditions = $this->objectManagerHelper->getObject('Magento\Rule\Model\Renderer\Conditions');
        $this->_element = $this->getMock(
            '\Magento\Framework\Data\Form\Element\AbstractElement',
            ['getRule'],
            [],
            '',
            false
        );
    }

    public function testRender()
    {
        $rule = $this->getMock('\Magento\Rule\Model\Rule', ['getConditions', '__sleep', '__wakeup'], [], '', false);
        $conditions = $this->getMock('\Magento\Rule\Model\Condition\Combine', ['asHtmlRecursive'], [], '', false);

        $this->_element->expects($this->any())
            ->method('getRule')
            ->will($this->returnValue($rule));

        $rule->expects($this->any())
            ->method('getConditions')
            ->will($this->returnValue($conditions));

        $conditions->expects($this->once())
            ->method('asHtmlRecursive')
            ->will($this->returnValue('conditions html'));

        $this->assertEquals('conditions html', $this->conditions->render($this->_element));
    }
}
