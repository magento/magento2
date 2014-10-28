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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Rule\Model\Condition;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class CombineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Condition\Combine
     */
    protected $_combine;

    /**
     * @var ObjectManagerHelper
     */
    protected $_objectManagerHelper;

    /**
     * @var \Magento\Rule\Model\Condition\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;

    protected function setUp()
    {
        $this->_objectManagerHelper = new ObjectManagerHelper($this);
        $this->_combine = $this->_objectManagerHelper->getObject('Magento\Rule\Model\Condition\Combine');
    }

    /**
     * @covers \Magento\Rule\Model\Condition\AbstractCondition::getValueName
     * @dataProvider optionValuesData
     * @param string|array $value
     * @param string $expectingData
     */
    public function testGetValueName($value, $expectingData)
    {
        $this->_combine->setValueOption(array('option_key' => 'option_value'))->setValue($value);
        $this->assertEquals($expectingData, $this->_combine->getValueName());
    }

    public function optionValuesData()
    {
        return array(
            array('option_key', 'option_value'),
            array('option_value', 'option_value'),
            array(array('option_key'), 'option_value'),
            array('', '...'),
        );
    }

}
