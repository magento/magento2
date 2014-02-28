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
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\RecurringProfile\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\RecurringProfile\Model\Observer
     */
    protected $_testModel;

    /**
     * @var \Magento\RecurringProfile\Block\Fields
     */
    protected $_fieldsBlock;

    /**
     * @var \Magento\RecurringProfile\Model\RecurringProfileFactory
     */
    protected $_recurringProfileFactory;

    /**
     * @var \Magento\Event
     */
    protected $_event;

    /**
     * @var \Magento\RecurringProfile\Model\ProfileFactory
     */
    protected $_profileFactory;

    /**
     * @var \Magento\RecurringProfile\Model\Profile
     */
    protected $_profile;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    protected $_quote;

    protected function setUp()
    {
        $this->_blockFactory = $this->getMock(
            'Magento\View\Element\BlockFactory', ['createBlock'], [], '', false
        );
        $this->_observer = $this->getMock('Magento\Event\Observer', [], [], '', false);
        $this->_fieldsBlock = $this->getMock(
            '\Magento\RecurringProfile\Block\Fields', ['getFieldLabel'], [], '', false
        );
        $this->_recurringProfileFactory = $this->getMock(
            '\Magento\RecurringProfile\Model\RecurringProfileFactory', ['create'], [], '', false
        );
        $this->_profileFactory = $this->getMock(
            '\Magento\RecurringProfile\Model\ProfileFactory', ['create', 'importProduct'], [], '', false
        );
        $this->_checkoutSession = $this->getMock(
            '\Magento\Checkout\Model\Session', ['setLastRecurringProfileIds'], [], '', false
        );
        $this->_quote = $this->getMock(
            '\Magento\RecurringProfile\Model\QuoteImporter',
            ['prepareRecurringPaymentProfiles'],
            [],
            '',
            false
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_testModel = $helper->getObject('Magento\RecurringProfile\Model\Observer', [
            'blockFactory' => $this->_blockFactory,
            'recurringProfileFactory' => $this->_recurringProfileFactory,
            'fields' => $this->_fieldsBlock,
            'profileFactory' => $this->_profileFactory,
            'checkoutSession' => $this->_checkoutSession,
            'quoteImporter' => $this->_quote
        ]);

        $this->_event = $this->getMock(
            'Magento\Event', [
                'getProductElement', 'getProduct', 'getResult', 'getBuyRequest', 'getQuote', 'getApi'
            ], [], '', false
        );

        $this->_observer->expects($this->any())->method('getEvent')->will($this->returnValue($this->_event));
        $this->_profile = $this->getMock('Magento\RecurringProfile\Model\Profile', [
            '__sleep', '__wakeup', 'isValid', 'importQuote', 'importQuoteItem', 'submit', 'getId', 'setMethodCode'
        ], [], '', false);
    }

    public function testPrepareProductRecurringProfileOptions()
    {
        $profile = $this->getMock(
            'Magento\Object',
            [
                'setStory',
                'importBuyRequest',
                'importProduct',
                'exportStartDatetime',
                'exportScheduleInfo',
                'getFieldLabel'
            ],
            [],
            '',
            false
        );
        $profile->expects($this->once())->method('exportStartDatetime')->will($this->returnValue('date'));
        $profile->expects($this->any())->method('setStore')->will($this->returnValue($profile));
        $profile->expects($this->once())->method('importBuyRequest')->will($this->returnValue($profile));
        $profile->expects($this->once())->method('exportScheduleInfo')
            ->will($this->returnValue([new \Magento\Object(['title' => 'Title', 'schedule' => 'schedule'])]));

        $this->_fieldsBlock->expects($this->once())->method('getFieldLabel')->will($this->returnValue('Field Label'));

        $this->_recurringProfileFactory->expects($this->once())->method('create')->will($this->returnValue($profile));

        $product = $this->getMock('Magento\Object', ['isRecurring', 'addCustomOption'], [], '', false);
        $product->expects($this->once())->method('isRecurring')->will($this->returnValue(true));

        $infoOptions = [
            ['label' => 'Field Label', 'value' => 'date'],
            ['label' => 'Title', 'value' => 'schedule']
        ];

        $product->expects($this->at(2))->method('addCustomOption')->with(
            'additional_options',
            serialize($infoOptions)
        );

        $this->_event->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $this->_testModel->prepareProductRecurringProfileOptions($this->_observer);
    }

    public function testRenderRecurringProfileForm()
    {
        $blockMock = $this->getMock(
            'Magento\View\Element\BlockInterface',
            [
                'setNameInLayout', 'setParentElement', 'setProductEntity', 'toHtml', 'addFieldMap',
                'addFieldDependence', 'addConfigOptions'
            ]
        );
        $map = [
            ['Magento\RecurringProfile\Block\Adminhtml\Profile\Edit\Form', [], $blockMock],
            ['Magento\Backend\Block\Widget\Form\Element\Dependence', [], $blockMock]
        ];
        $profileElement = $this->getMock('Magento\Data\Form\Element\AbstractElement', [], [], '', false);
        $this->_event->expects($this->once())->method('getProductElement')->will($this->returnValue($profileElement));
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->_event->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $this->_blockFactory->expects($this->any())->method('createBlock')->will($this->returnValueMap($map));
        $blockMock->expects($this->any())->method('setNameInLayout');
        $blockMock->expects($this->once())->method('setParentElement')->with($profileElement);
        $blockMock->expects($this->once())->method('setProductEntity')->with($product);
        $blockMock->expects($this->exactly(2))->method('toHtml')->will($this->returnValue('html'));
        $blockMock->expects($this->once())->method('addConfigOptions')->with(['levels_up' => 2]);
        $result = new \StdClass();
        $this->_event->expects($this->once())->method('getResult')->will($this->returnValue($result));
        $this->_testModel->addFieldsToProductEditForm($this->_observer);
        $this->assertEquals('htmlhtml', $result->output);
    }

    public function testSubmitRecurringPaymentProfiles()
    {
        $this->_prepareRecurringPaymentProfiles();
        $this->_quote->expects($this->once())->method('prepareRecurringPaymentProfiles')
            ->will($this->returnValue([$this->_profile]));

        $this->_profile->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->_profile->expects($this->once())->method('submit');

        $this->_testModel->submitRecurringPaymentProfiles($this->_observer);
    }

    public function testAddRecurringProfileIdsToSession()
    {
        $this->_prepareRecurringPaymentProfiles();

        $this->_testModel->addRecurringProfileIdsToSession($this->_observer);
    }

    protected function _prepareRecurringPaymentProfiles()
    {
        $product = $this->getMock('Magento\RecurringProfile\Model\Profile', [
            'isRecurring', '__sleep', '__wakeup'
        ], [], '', false);
        $product->expects($this->any())->method('isRecurring')->will($this->returnValue(true));

        $this->_profile = $this->getMock('Magento\RecurringProfile\Model\Profile', [
            '__sleep', '__wakeup', 'isValid', 'importQuote', 'importQuoteItem', 'submit', 'getId', 'setMethodCode'
        ], [], '', false);

        $quote = $this->getMock('Magento\Sales\Model\Quote', [
            'getTotalsCollectedFlag', '__sleep', '__wakeup', 'getAllVisibleItems'
        ], [], '', false);

        $this->_event->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
    }
}
