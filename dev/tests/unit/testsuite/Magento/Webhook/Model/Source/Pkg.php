<?php
/**
 * Parent class for Source tests that provides common functionality.
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
 * @category    Magento
 * @package     Magento_Webhook
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Source;

class Pkg extends \PHPUnit_Framework_TestCase
{
    /** Config values */
    const CONFIG_LABEL = 'blah';
    const CONFIG_STATUS = 'enabled';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockConfig;
    
    /** @var \Magento\Core\Model\Config\Element */
    protected $_modelConfigElement;
    
    protected function setUp()
    {
        $label = self::CONFIG_LABEL;
        $status = self::CONFIG_STATUS;
        $this->_modelConfigElement = new \Magento\Core\Model\Config\Element(
            "<types><type><status>{$status}</status><label>{$label}</label></type></types>"
        );
        $this->_mockConfig = $this->getMockBuilder('Magento\Core\Model\Config')
            ->disableOriginalConstructor()->getMock();
        $this->_mockConfig->expects($this->any())
            ->method('getNode')
            ->will($this->returnValue($this->_modelConfigElement));
    }

    /**
     * Asserts that the elements array contains the expected label and value.
     *
     * @param $elements
     */
    protected function _assertElements($elements)
    {
        $this->assertSame(self::CONFIG_LABEL, $elements[0]['label']);
        $this->assertSame('type', $elements[0]['value']);
    }
}
