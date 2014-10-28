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

/**
 * Test class for \Magento\Rule\Model\Condition\AbstractCondition
 */
namespace Magento\Rule\Model\Condition;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValueElement()
    {
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $context = $objectManager->create('Magento\Rule\Model\Condition\Context', array('layout' => $layoutMock));

        /** @var \Magento\Rule\Model\Condition\AbstractCondition $model */
        $model = $this->getMockForAbstractClass(
            'Magento\Rule\Model\Condition\AbstractCondition',
            array($context),
            '',
            true,
            true,
            true,
            array('getValueElementRenderer')
        );
        $editableBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Rule\Block\Editable'
        );
        $model->expects($this->any())->method('getValueElementRenderer')->will($this->returnValue($editableBlock));

        $rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Rule\Model\Rule');
        $model->setRule(
            $rule->setForm(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Framework\Data\Form')
            )
        );

        $property = new \ReflectionProperty('Magento\Rule\Model\Condition\AbstractCondition', '_inputType');
        $property->setAccessible(true);
        $property->setValue($model, 'date');

        $element = $model->getValueElement();
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
