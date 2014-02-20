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
 * @category    Magento
 * @package     Magento_Paypal
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Paypal\Model\Payflowlink
 *
 */
namespace Magento\Paypal\Model;

class PayflowlinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $_modelClass;

    /**
     * Paypal sent request
     *
     * @var \Magento\Object
     */
    static public $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleListMock;

    /**#@+
     *
     * Test response parameters
     */
    const PARAMETER_FIRSTNAME = 'Firstname';
    const PARAMETER_LASTNAME = 'Lastname';
    const PARAMETER_ADDRESS = '111 Streetname Street';
    const PARAMETER_CITY = 'City';
    const PARAMETER_STATE = 'State';
    const PARAMETER_ZIP = '11111';
    const PARAMETER_COUNTRY = 'Country';
    const PARAMETER_PHONE = '111-11-11';
    const PARAMETER_EMAIL = 'email@example.com';
    const PARAMETER_NAMETOSHIP = 'Name to ship';
    const PARAMETER_ADDRESSTOSHIP = '112 Streetname Street';
    const PARAMETER_CITYTOSHIP = 'City to ship';
    const PARAMETER_STATETOSHIP = 'State to ship';
    const PARAMETER_ZIPTOSHIP = '22222';
    const PARAMETER_COUNTRYTOSHIP = 'Country to ship';
    const PARAMETER_PHONETOSHIP = '222-22-22';
    const PARAMETER_EMAILTOSHIP = 'emailtoship@example.com';
    const PARAMETER_FAXTOSHIP = '333-33-33';
    const PARAMETER_METHOD = 'CC';
    const PARAMETER_CSCMATCH = 'Y';
    const PARAMETER_AVSADDR = 'X';
    const PARAMETER_AVSZIP = 'N';
    const PARAMETER_TYPE = 'A';
    /**#@-*/

    protected function setUp()
    {
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $payment = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(array('getOrder', '__wakeup'))
            ->getMock();
        $payment->expects($this->any())
            ->method('getOrder', '__wakeup')
            ->will($this->returnValue($order));
        $request = new \Magento\Paypal\Model\Payflow\Request;
        $this->_modelClass = $this->getMock(
            'Magento\Paypal\Model\Payflowlink',
            array(
                'getResponse',
                '_postRequest',
                '_processTokenErrors',
                'getInfoInstance',
                '_generateSecureSilentPostHash',
                '_buildTokenRequest',
                '_getCallbackUrl'
            ),
            array(), '', false
        );
        $this->_modelClass->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($request));
        $this->_modelClass->expects($this->any())
            ->method('getInfoInstance')
            ->will($this->returnValue($payment));
        $this->_modelClass->expects($this->any())
            ->method('_generateSecureSilentPostHash')
            ->will($this->returnValue(md5('1234567890')));
        $this->_modelClass->expects($this->any())
            ->method('_postRequest')
            ->will($this->returnValue(true));
        $this->_modelClass->expects($this->any())
            ->method('_processTokenErrors')
            ->will($this->returnValue(true));
    }

    public function testSetResponseData()
    {
        // Setting legacy parameters
        /** @var $model \Magento\Paypal\Model\Payflowlink */
        $model = $this->_modelClass;
        $model->setResponseData(array(
            'NAME' => self::PARAMETER_FIRSTNAME . ' ' . self::PARAMETER_LASTNAME,
            'FIRSTNAME' => self::PARAMETER_FIRSTNAME,
            'LASTNAME' => self::PARAMETER_LASTNAME,
            'ADDRESS' => self::PARAMETER_ADDRESS,
            'CITY' => self::PARAMETER_CITY,
            'STATE' => self::PARAMETER_STATE,
            'ZIP' => self::PARAMETER_ZIP,
            'COUNTRY' => self::PARAMETER_COUNTRY,
            'PHONE' => self::PARAMETER_PHONE,
            'EMAIL' => self::PARAMETER_EMAIL,
            'NAMETOSHIP' => self::PARAMETER_NAMETOSHIP,
            'ADDRESSTOSHIP' => self::PARAMETER_ADDRESSTOSHIP,
            'CITYTOSHIP' => self::PARAMETER_CITYTOSHIP,
            'STATETOSHIP' => self::PARAMETER_STATETOSHIP,
            'ZIPTOSHIP' => self::PARAMETER_ZIPTOSHIP,
            'COUNTRYTOSHIP' => self::PARAMETER_COUNTRYTOSHIP,
            'PHONETOSHIP' => self::PARAMETER_PHONETOSHIP,
            'EMAILTOSHIP' => self::PARAMETER_EMAILTOSHIP,
            'FAXTOSHIP' => self::PARAMETER_FAXTOSHIP,
            'METHOD' => self::PARAMETER_METHOD,
            'CSCMATCH' => self::PARAMETER_CSCMATCH,
            'AVSDATA' => self::PARAMETER_AVSADDR . self::PARAMETER_AVSZIP,
            'TYPE' => self::PARAMETER_TYPE,
        ));

        $this->_assertResponseData($model);

        // Setting new parameters
        /** @var $model \Magento\Paypal\Model\Payflowlink */
        $model = $this->_modelClass;
        $model->setResponseData(array(
            'BILLTOFIRSTNAME' => self::PARAMETER_FIRSTNAME,
            'BILLTOLASTNAME' => self::PARAMETER_LASTNAME,
            'BILLTOSTREET' => self::PARAMETER_ADDRESS,
            'BILLTOCITY' => self::PARAMETER_CITY,
            'BILLTOSTATE' => self::PARAMETER_STATE,
            'BILLTOZIP' => self::PARAMETER_ZIP,
            'BILLTOCOUNTRY' => self::PARAMETER_COUNTRY,
            'BILLTOPHONE' => self::PARAMETER_PHONE,
            'BILLTOEMAIL' => self::PARAMETER_EMAIL,
            'SHIPTOFIRSTNAME' => self::PARAMETER_NAMETOSHIP,
            'SHIPTOSTREET' => self::PARAMETER_ADDRESSTOSHIP,
            'SHIPTOCITY' => self::PARAMETER_CITYTOSHIP,
            'SHIPTOSTATE' => self::PARAMETER_STATETOSHIP,
            'SHIPTOZIP' => self::PARAMETER_ZIPTOSHIP,
            'SHIPTOCOUNTRY' => self::PARAMETER_COUNTRYTOSHIP,
            'SHIPTOPHONE' => self::PARAMETER_PHONETOSHIP,
            'SHIPTOEMAIL' => self::PARAMETER_EMAILTOSHIP,
            'SHIPTOFAX' => self::PARAMETER_FAXTOSHIP,
            'TENDER' => self::PARAMETER_METHOD,
            'CVV2MATCH' => self::PARAMETER_CSCMATCH,
            'AVSADDR' => self::PARAMETER_AVSADDR,
            'AVSZIP' => self::PARAMETER_AVSZIP,
            'TRXTYPE' => self::PARAMETER_TYPE,
        ));
        $this->_assertResponseData($model);
    }

    /**
     * @dataProvider defaultRequestParameters
     */
    public function testDefaultRequestParameters($cscrequired, $cscedit, $emailcustomer, $urlmethod)
    {
        $params = array($cscrequired, $cscedit, $emailcustomer, $urlmethod);
        /** @var $model \Magento\Paypal\Model\Payflowlink */
        $model = $this->_modelClass;
        $this->_prepareRequest($model, $params);

        // check whether all parameters were sent
        $request = \Magento\Paypal\Model\PayflowlinkTest::$request;
        $this->_assertRequestBaseParameters($model);
        $this->assertEquals($cscrequired, $request->getCscrequired());
        $this->assertEquals($cscedit, $request->getCscedit());
        $this->assertEquals($emailcustomer, $request->getEmailcustomer());
        $this->assertEquals($urlmethod, $request->getUrlmethod());
    }

    /**
     * Prepare request for test
     *
     * @param \Magento\Paypal\Model\Payflowlink $model
     * @param array() $params
     */
    protected function _prepareRequest(\Magento\Paypal\Model\Payflowlink $model, $params)
    {
        $request = new \Magento\Paypal\Model\Payflow\Request;
        $request->setCancelurl('/paypal/' . $model->getCallbackController() . '/' . 'cancelPayment')
            ->setErrorurl('/paypal/' . $model->getCallbackController() . '/' . 'returnUrl')
            ->setSilentpost('TRUE')
            ->setSilentposturl('/paypal/' . $model->getCallbackController() . '/' . 'silentPost')
            ->setReturnurl('/paypal/' . $model->getCallbackController() . '/' . 'returnUrl')
            ->setTemplate('minLayout')
            ->setDisablereceipt('TRUE')
            ->setCscrequired($params[0])
            ->setCscedit($params[1])
            ->setEmailcustomer($params[2])
            ->setUrlmethod($params[3]);
        $model->expects($this->any())
            ->method('_buildTokenRequest')
            ->will($this->returnValue($request));

        $checkRequest = create_function('$request', 'Magento\Paypal\Model\PayflowlinkTest::$request = $request;');
        $model->expects($this->any())->method('_postRequest')->will($this->returnCallback($checkRequest));
        \Magento\Paypal\Model\PayflowlinkTest::$request = null;
        $model->initialize(\Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH, new \Magento\Object());
    }

    /**
     * Assert request not configurable parameters
     *
     * @param \Magento\Paypal\Model\Payflowlink $model
     */
    protected function _assertRequestBaseParameters(\Magento\Paypal\Model\Payflowlink $model)
    {
        $controllerPath = '/paypal/' . $model->getCallbackController() . '/';
        $request = \Magento\Paypal\Model\PayflowlinkTest::$request;
        $this->assertEquals($controllerPath . 'cancelPayment', $request->getData('cancelurl'));
        $this->assertEquals($controllerPath . 'returnUrl', $request->getData('errorurl'));
        $this->assertEquals($controllerPath . 'silentPost', $request->getData('silentposturl'));
        $this->assertEquals($controllerPath . 'returnUrl', $request->getData('returnurl'));
        $this->assertEquals(\Magento\Paypal\Model\Payflowlink::LAYOUT_TEMPLATE, $request->getData('template'));
        $this->assertEquals('TRUE', $request->getData('silentpost'));
        $this->assertEquals('TRUE', $request->getData('disablereceipt'));
    }

    /**
     * Assert response data
     *
     * @param \Magento\Paypal\Model\Payflowlink $model
     */
    protected function _assertResponseData(\Magento\Paypal\Model\Payflowlink $model)
    {
        $data = $model->getResponse()->getData();
        $this->assertEquals(self::PARAMETER_FIRSTNAME . ' ' . self::PARAMETER_LASTNAME, $data['name']);
        $this->assertEquals(self::PARAMETER_FIRSTNAME, $data['firstname']);
        $this->assertEquals(self::PARAMETER_LASTNAME, $data['lastname']);
        $this->assertEquals(self::PARAMETER_ADDRESS, $data['address']);
        $this->assertEquals(self::PARAMETER_CITY, $data['city']);
        $this->assertEquals(self::PARAMETER_STATE, $data['state']);
        $this->assertEquals(self::PARAMETER_ZIP, $data['zip']);
        $this->assertEquals(self::PARAMETER_COUNTRY, $data['country']);
        $this->assertEquals(self::PARAMETER_PHONE, $data['phone']);
        $this->assertEquals(self::PARAMETER_EMAIL, $data['email']);
        $this->assertEquals(self::PARAMETER_NAMETOSHIP, $data['nametoship']);
        $this->assertEquals(self::PARAMETER_ADDRESSTOSHIP, $data['addresstoship']);
        $this->assertEquals(self::PARAMETER_CITYTOSHIP, $data['citytoship']);
        $this->assertEquals(self::PARAMETER_STATETOSHIP, $data['statetoship']);
        $this->assertEquals(self::PARAMETER_ZIPTOSHIP, $data['ziptoship']);
        $this->assertEquals(self::PARAMETER_COUNTRYTOSHIP, $data['countrytoship']);
        $this->assertEquals(self::PARAMETER_PHONETOSHIP, $data['phonetoship']);
        $this->assertEquals(self::PARAMETER_EMAILTOSHIP, $data['emailtoship']);
        $this->assertEquals(self::PARAMETER_FAXTOSHIP, $data['faxtoship']);
        $this->assertEquals(self::PARAMETER_METHOD, $data['method']);
        $this->assertEquals(self::PARAMETER_CSCMATCH, $data['cscmatch']);
        $this->assertEquals(self::PARAMETER_AVSADDR . self::PARAMETER_AVSZIP, $data['avsdata']);
        $this->assertEquals(self::PARAMETER_TYPE, $data['type']);
    }

    /**
     * Data Provider for test defaultRequestParameters
     *
     * @return array
     */
    public function defaultRequestParameters()
    {
        return array(
            array('TRUE', 'TRUE', 'FALSE', 'GET'),
            array('FALSE', 'FALSE', 'TRUE', 'POST'),
        );
    }
}
