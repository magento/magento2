<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
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
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var Group|MockObject
     */
    protected $_group;

    /**
     * @var Config|MockObject
     */
    protected $_backendConfig;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_group = $this->createMock(Group::class);
        $this->_element = $this->getMockForAbstractClass(
            AbstractElement::class,
            [],
            '',
            false,
            true,
            true,
            ['getHtmlId', 'getElementHtml', 'getName', 'getElements', 'getId']
        );
        $this->_element->expects($this->any())
            ->method('getHtmlId')
            ->willReturn('html id');
        $this->_element->expects($this->any())
            ->method('getElementHtml')
            ->willReturn('element html');
        $this->_element->expects($this->any())
            ->method('getName')
            ->willReturn('name');
        $this->_element->expects($this->any())
            ->method('getElements')
            ->willReturn([]);
        $this->_element->expects($this->any())
            ->method('getId')
            ->willReturn('id');
        $this->_backendConfig = $this->createMock(Config::class);
        $this->_model = $helper->getObject(
            Payment::class,
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
            ->willReturnMap(
                [[self::CONFIG_PATH_ACTIVE, null, null, '1'], [self::CONFIG_PATH_NOT_ACTIVE, null, null, '0']]
            );
        $html = $this->_model->render($this->_element);
        $this->assertStringContainsString($expected, $html);
    }

    /**
     * @return array
     */
    public static function isPaymentEnabledDataProvider()
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
                    'fieldset_css' => 'any-css',
                ],
                ' class="section-config any-css with-button enabled">'
            ],
        ];
    }
}
