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
 * Test class for \Magento\Centinel\Model\State\Jcb.
 */
namespace Magento\Centinel\Model\State;

class JcbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * State model
     *
     * @var \Magento\Centinel\Model\State\Jcb
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Centinel\Model\State\Jcb();
        $this->_model->setDataStorage(new \Magento\Framework\Object());
    }

    /**
     * @param bool $result
     * @param array $lookupResults
     * @param array $params
     * @dataProvider testIsAuthenticateAllowedDataProvider
     */
    public function testIsAuthenticateAllowed($result, $lookupResults, $params)
    {
        $this->_model->setLookupResult(new \Magento\Framework\Object($lookupResults));
        $this->_model->setAuthenticateResult(new \Magento\Framework\Object($params));
        $this->assertEquals($result, $this->_model->isAuthenticateAllowed());
    }

    public function testIsAuthenticateAllowedDataProvider()
    {
        return array(
            'successful lookup and empty authentication' => array(
                true,
                array(
                    'enrolled' => 'Y',
                    'acs_url' => 'no empty value',
                    'payload' => 'no empty value',
                    'error_no' => '0'
                ),
                array()
            ),
            'wrong lookup and empty authentication' => array(false, array(), array()),
            'successful lookup and not empty authentication' => array(
                false,
                array(
                    'enrolled' => 'Y',
                    'acs_url' => 'no empty value',
                    'payload' => 'no empty value',
                    'error_no' => '0'
                ),
                array('eci_flag' => 'value')
            ),
            'wrong lookup and not empty authentication' => array(false, array(), array('eci_flag' => 'value'))
        );
    }

    public function testIsAuthenticateSuccessfulWithSoftLookup()
    {
        $lookupResults = array('enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => '0');
        $this->_model->setLookupResult(new \Magento\Framework\Object($lookupResults));

        $this->_model->setIsModeStrict(true);
        $this->assertEquals(false, $this->_model->isAuthenticateSuccessful());

        $this->_model->setIsModeStrict(false);
        $this->assertEquals(true, $this->_model->isAuthenticateSuccessful());
    }

    /**
     * @param bool $result
     * @param bool $strictMode
     * @param array $params
     * @dataProvider isAuthenticateSuccessfulDataProvider
     */
    public function testIsAuthenticateSuccessful($result, $strictMode, $params)
    {
        $strictMode = $strictMode;
        // PHPMD bug: unused local variable warning
        $this->_model->setIsModeStrict($strictMode);
        $lookupResult = array(
            'enrolled' => 'Y',
            'acs_url' => 'no empty value',
            'payload' => 'no empty value',
            'error_no' => '0'
        );
        $this->_model->setLookupResult(new \Magento\Framework\Object($lookupResult));
        $this->_model->setAuthenticateResult(new \Magento\Framework\Object($params));

        $this->assertEquals($result, $this->_model->isAuthenticateSuccessful());
    }

    public function isAuthenticateSuccessfulDataProvider()
    {
        return array_merge(
            $this->_getAuthenticationSuccessfulData(),
            $this->_getAuthenticationUnavailableData(),
            $this->_getProcessingAttemptsPerformedData(),
            array(
                'Centinel case 2' => array(
                    false,
                    true,
                    array(
                        'pa_res_status' => 'Y',
                        'eci_flag' => '05',
                        'xid' => 'some string',
                        'cavv' => 'some string',
                        'error_no' => '0',
                        'signature_verification' => 'N'
                    )
                ),
                'Centinel case 3' => array(
                    false,
                    true,
                    array(
                        'pa_res_status' => 'N',
                        'signature_verification' => 'Y',
                        'eci_flag' => '07',
                        'xid' => 'some string',
                        'cavv' => '',
                        'error_no' => '0'
                    )
                ),
                'Centinel case 10' => array(
                    false,
                    true,
                    array(
                        'pa_res_status' => '',
                        'signature_verification' => '',
                        'eci_flag' => '07',
                        'xid' => '',
                        'cavv' => '',
                        'error_no' => 'not zero'
                    )
                )
            )
        );
    }

    /**
     * Data for successful authentication
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getAuthenticationSuccessfulData()
    {
        return array(
            'Centinel case 1' => array(
                true,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 pa_res_status is absent' => array(
                false,
                true,
                array(
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 eci_flag is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 xid is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 cavv is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 error_no is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 signature_verification is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 1 wrong pa_res_status' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'wrong value',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 wrong eci_flag' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => 'wrong value',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 empty xid' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => '',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 empty cavv' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 no zero error_no' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => 'no zero',
                    'signature_verification' => 'Y'
                )
            ),
            'Centinel case 1 wrong signature_verification' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'wrong value'
                )
            ),
            'Centinel case 1 no params' => array(false, true, array())
        );
    }

    /**
     * Data for unavailable authentication
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getAuthenticationUnavailableData()
    {
        return array(
            'Centinel case 4,5 in strict mode' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5' => array(
                true,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 pa_res_status is absent' => array(
                false,
                false,
                array(
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 signature_verification is absent' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 eci_flag is absent' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 xid is absent' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 cavv is absent' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 error_no is absent' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => ''
                )
            ),
            'Centinel case 4,5 wrong pa_res_status' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'wrong value',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 wrong signature_verification' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'wrong value',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 wrong eci_flag' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => 'wrong value',
                    'xid' => '',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 empty xid' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => '',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 not empty cavv' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => 'not empty',
                    'error_no' => '0'
                )
            ),
            'Centinel case 4,5 no zero error_no' => array(
                false,
                false,
                array(
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => 'no zero'
                )
            ),
            'Centinel case 4,5 no params' => array(false, false, array())
        );
    }

    /**
     * Data for attempts performed processing
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getProcessingAttemptsPerformedData()
    {
        return array(
            'Centinel case 11' => array(
                true,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 pa_res_status is absent' => array(
                false,
                true,
                array(
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 signature_verification is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 eci_flag is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 xid is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 cavv is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 error_no is absent' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string'
                )
            ),
            'Centinel case no params' => array(false, true, array()),
            'Centinel case 11 wrong pa_res_status' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'wrong value',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 wrong signature_verification' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'wrong value',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 wrong eci_flag' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => 'wrong value',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 empty xid' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => '',
                    'cavv' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 empty cavv' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                )
            ),
            'Centinel case 11 no zero' => array(
                false,
                true,
                array(
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => 'no zero'
                )
            )
        );
    }

    /**
     * @param bool $result
     * @param bool $strictMode
     * @param array $params
     * @dataProvider isLookupSuccessfulDataProvider
     */
    public function testIsLookupSuccessful($result, $strictMode, $params)
    {
        $this->_model->setLookupResult(new \Magento\Framework\Object($params));
        $this->_model->setIsModeStrict($strictMode);
        $this->assertEquals($result, $this->_model->isLookupSuccessful());
    }

    public function isLookupSuccessfulDataProvider()
    {
        return array_merge($this->_getSoftSuccessfulLookupData(), $this->_getStrictSuccessfulLookupData());
    }

    /**
     * Data for soft successful lookup
     *
     * @return array
     */
    protected function _getSoftSuccessfulLookupData()
    {
        return array(
            'Centinel case 5' => array(
                true,
                false,
                array('enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 5 enrolled is absent' => array(
                false,
                false,
                array('acs_url' => '', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 5 acs_url is absent' => array(
                false,
                false,
                array('enrolled' => '', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 5 payload is absent' => array(
                false,
                false,
                array('enrolled' => '', 'acs_url' => '', 'error_no' => '0')
            ),
            'Centinel case 5 error_no is absent' => array(
                false,
                false,
                array('enrolled' => '', 'acs_url' => '', 'payload' => '')
            ),
            'Centinel case 5 no params' => array(false, false, array()),
            'Centinel case 5 not empty enrolled' => array(
                false,
                false,
                array('enrolled' => 'not empty', 'acs_url' => '', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 5 not empty acs_url' => array(
                false,
                false,
                array('enrolled' => '', 'acs_url' => 'not empty', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 5 not empty payload' => array(
                false,
                false,
                array('enrolled' => '', 'acs_url' => '', 'payload' => 'not empty', 'error_no' => '0')
            ),
            'Centinel case 5 no zero error_no' => array(
                false,
                false,
                array('enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => 'not zero')
            ),
            'Centinel case 5 empty error_no' => array(
                false,
                false,
                array('enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => '')
            ),
            'Centinel case 7' => array(
                true,
                false,
                array('enrolled' => 'U', 'acs_url' => '', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 8,9' => array(
                true,
                false,
                array('enrolled' => 'U', 'acs_url' => '', 'payload' => '', 'error_no' => 'some string')
            ),
            'Centinel case 7,8,9 enrolled is absent' => array(
                false,
                false,
                array('acs_url' => '', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 7,8,9 acs_url is absent' => array(
                false,
                false,
                array('enrolled' => 'U', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 7,8,9 payload is absent' => array(
                false,
                false,
                array('enrolled' => 'U', 'acs_url' => '', 'error_no' => '0')
            ),
            'Centinel case 7,8,9 error_no no params' => array(false, false, array()),
            'Centinel case 7,8,9 wrong enrolled' => array(
                false,
                false,
                array('enrolled' => 'wrong value', 'acs_url' => '', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 7,8,9 not empty acs_url' => array(
                false,
                false,
                array('enrolled' => 'U', 'acs_url' => 'not empty', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel case 7,8,9 not empty payload' => array(
                false,
                false,
                array('enrolled' => 'U', 'acs_url' => '', 'payload' => 'not empty', 'error_no' => '0')
            )
        );
    }

    /**
     * Data for strict successful lookup
     *
     * @return array
     */
    protected function _getStrictSuccessfulLookupData()
    {
        return array(
            'Centinel cases 1-4, 6, 10-11' => array(
                true,
                true,
                array('enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => 'some string', 'error_no' => '0')
            ),
            'Centinel cases 1-4, 6, 10-11 enrolled is absent' => array(
                false,
                true,
                array('acs_url' => 'some string', 'payload' => 'some string', 'error_no' => '0')
            ),
            'Centinel cases 1-4, 6, 10-11 acs_url is absent' => array(
                false,
                true,
                array('enrolled' => 'Y', 'payload' => 'some string', 'error_no' => '0')
            ),
            'Centinel cases 1-4, 6, 10-11 payload is absent' => array(
                false,
                true,
                array('enrolled' => 'Y', 'acs_url' => 'some string', 'error_no' => '0')
            ),
            'Centinel cases 1-4, 6, 10-11 error_no is absent' => array(
                false,
                true,
                array('enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => 'some string')
            ),
            'Centinel cases 1-4, 6, 10-11 no params' => array(false, true, array()),
            'Centinel cases 1-4, 6, 10-11 wrong enrolled' => array(
                false,
                true,
                array(
                    'enrolled' => 'wrong value',
                    'acs_url' => 'some string',
                    'payload' => 'some string',
                    'error_no' => '0'
                )
            ),
            'Centinel cases 1-4, 6, 10-11 empty enrolled' => array(
                false,
                true,
                array('enrolled' => '', 'acs_url' => 'some string', 'payload' => 'some string', 'error_no' => '0')
            ),
            'Centinel cases 1-4, 6, 10-11 empty acs_url' => array(
                false,
                true,
                array('enrolled' => 'Y', 'acs_url' => '', 'payload' => 'some string', 'error_no' => '0')
            ),
            'Centinel cases 1-4, 6, 10-11 empty payload' => array(
                false,
                true,
                array('enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => '', 'error_no' => '0')
            ),
            'Centinel cases 1-4, 6, 10-11 wrong error_no' => array(
                false,
                true,
                array(
                    'enrolled' => 'Y',
                    'acs_url' => 'some string',
                    'payload' => 'some string',
                    'error_no' => 'wrong value'
                )
            ),
            'Centinel cases 1-4, 6, 10-11 empty error_no' => array(
                false,
                true,
                array('enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => 'some string', 'error_no' => '')
            )
        );
    }
}
