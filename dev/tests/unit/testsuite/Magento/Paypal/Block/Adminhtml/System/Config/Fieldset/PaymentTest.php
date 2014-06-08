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
namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Activity config path
     */
    const CONFIG_PATH_ACTIVE = 'payment/path/active';
    const CONFIG_PATH_NOT_ACTIVE = 'payment/path/not-active';
    /**#@-*/

    /**
     * @var Payment
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_group;

    /**
     * @var \Magento\Backend\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendConfig;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_group = $this->getMock('Magento\Backend\Model\Config\Structure\Element\Group', [], [], '', false);
        $this->_element = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            [],
            '',
            false,
            true,
            true,
            ['getHtmlId', 'getElementHtml', 'getName', 'getElements', 'getId']
        );
        $this->_element->expects($this->any())
            ->method('getHtmlId')
            ->will($this->returnValue('html id'));
        $this->_element->expects($this->any())
            ->method('getElementHtml')
            ->will($this->returnValue('element html'));
        $this->_element->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('name'));
        $this->_element->expects($this->any())
            ->method('getElements')
            ->will($this->returnValue([]));
        $this->_element->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('id'));
        $this->_backendConfig = $this->getMock('Magento\Backend\Model\Config', [], [], '', false);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Payment',
            ['backendConfig' => $this->_backendConfig]
        );
        $this->_model->setGroup($this->_group);
    }

    /**
     * @dataProvider isPaymentEnabledDataProvider
     */
    public function testIsPaymentEnabled($groupConfig, $expected)
    {
        $this->_element->setGroup($groupConfig);
        $this->_backendConfig->expects($this->any())
            ->method('getConfigDataValue')
            ->will($this->returnValueMap(
                [[self::CONFIG_PATH_ACTIVE, null, null, '1'], [self::CONFIG_PATH_NOT_ACTIVE, null, null, '0']]
            ));
        $html = $this->_model->render($this->_element);
        $this->assertContains($expected, $html);
    }

    public function isPaymentEnabledDataProvider()
    {
        return [
            [[], ' class="section-config with-button">'],
            [['fieldset_css' => 'any-css'], ' class="section-config any-css with-button">'],
            [['activity_path' => self::CONFIG_PATH_ACTIVE], ' class="section-config with-button enabled">'],
            [['activity_path' => self::CONFIG_PATH_NOT_ACTIVE], ' class="section-config with-button">'],
            [['activity_path' => [self::CONFIG_PATH_ACTIVE]], ' class="section-config with-button enabled">'],
            [['activity_path' => [self::CONFIG_PATH_NOT_ACTIVE]], ' class="section-config with-button">'],
            [
                ['activity_path' => [self::CONFIG_PATH_ACTIVE, self::CONFIG_PATH_NOT_ACTIVE]],
                ' class="section-config with-button enabled">'
            ],
            [
                ['activity_path' => self::CONFIG_PATH_ACTIVE, 'fieldset_css' => 'any-css'],
                ' class="section-config any-css with-button enabled">'
            ],
            [
                ['activity_path' => self::CONFIG_PATH_NOT_ACTIVE, 'fieldset_css' => 'any-css'],
                ' class="section-config any-css with-button">'
            ],
            [
                ['activity_path' => [self::CONFIG_PATH_ACTIVE], 'fieldset_css' => 'any-css'],
                ' class="section-config any-css with-button enabled">'
            ],
            [
                ['activity_path' => [self::CONFIG_PATH_NOT_ACTIVE], 'fieldset_css' => 'any-css'],
                ' class="section-config any-css with-button">'
            ],
            [
                [
                    'activity_path' => [self::CONFIG_PATH_ACTIVE, self::CONFIG_PATH_NOT_ACTIVE],
                    'fieldset_css' => 'any-css'
                ],
                ' class="section-config any-css with-button enabled">'
            ],
        ];
    }
}
