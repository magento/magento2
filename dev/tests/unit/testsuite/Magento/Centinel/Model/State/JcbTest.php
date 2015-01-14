<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        return [
            'successful lookup and empty authentication' => [
                true,
                [
                    'enrolled' => 'Y',
                    'acs_url' => 'no empty value',
                    'payload' => 'no empty value',
                    'error_no' => '0'
                ],
                [],
            ],
            'wrong lookup and empty authentication' => [false, [], []],
            'successful lookup and not empty authentication' => [
                false,
                [
                    'enrolled' => 'Y',
                    'acs_url' => 'no empty value',
                    'payload' => 'no empty value',
                    'error_no' => '0'
                ],
                ['eci_flag' => 'value'],
            ],
            'wrong lookup and not empty authentication' => [false, [], ['eci_flag' => 'value']]
        ];
    }

    public function testIsAuthenticateSuccessfulWithSoftLookup()
    {
        $lookupResults = ['enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => '0'];
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
        $lookupResult = [
            'enrolled' => 'Y',
            'acs_url' => 'no empty value',
            'payload' => 'no empty value',
            'error_no' => '0',
        ];
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
            [
                'Centinel case 2' => [
                    false,
                    true,
                    [
                        'pa_res_status' => 'Y',
                        'eci_flag' => '05',
                        'xid' => 'some string',
                        'cavv' => 'some string',
                        'error_no' => '0',
                        'signature_verification' => 'N'
                    ],
                ],
                'Centinel case 3' => [
                    false,
                    true,
                    [
                        'pa_res_status' => 'N',
                        'signature_verification' => 'Y',
                        'eci_flag' => '07',
                        'xid' => 'some string',
                        'cavv' => '',
                        'error_no' => '0'
                    ],
                ],
                'Centinel case 10' => [
                    false,
                    true,
                    [
                        'pa_res_status' => '',
                        'signature_verification' => '',
                        'eci_flag' => '07',
                        'xid' => '',
                        'cavv' => '',
                        'error_no' => 'not zero'
                    ],
                ]
            ]
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
        return [
            'Centinel case 1' => [
                true,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 pa_res_status is absent' => [
                false,
                true,
                [
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 eci_flag is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 xid is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 cavv is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 error_no is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 signature_verification is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 1 wrong pa_res_status' => [
                false,
                true,
                [
                    'pa_res_status' => 'wrong value',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 wrong eci_flag' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => 'wrong value',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 empty xid' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => '',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 empty cavv' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 no zero error_no' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => 'no zero',
                    'signature_verification' => 'Y'
                ],
            ],
            'Centinel case 1 wrong signature_verification' => [
                false,
                true,
                [
                    'pa_res_status' => 'Y',
                    'eci_flag' => '05',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0',
                    'signature_verification' => 'wrong value'
                ],
            ],
            'Centinel case 1 no params' => [false, true, []]
        ];
    }

    /**
     * Data for unavailable authentication
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getAuthenticationUnavailableData()
    {
        return [
            'Centinel case 4,5 in strict mode' => [
                false,
                true,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5' => [
                true,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 pa_res_status is absent' => [
                false,
                false,
                [
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 signature_verification is absent' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 eci_flag is absent' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 xid is absent' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 cavv is absent' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 error_no is absent' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => ''
                ],
            ],
            'Centinel case 4,5 wrong pa_res_status' => [
                false,
                false,
                [
                    'pa_res_status' => 'wrong value',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 wrong signature_verification' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'wrong value',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 wrong eci_flag' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => 'wrong value',
                    'xid' => '',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 empty xid' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => '',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 not empty cavv' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => 'not empty',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 4,5 no zero error_no' => [
                false,
                false,
                [
                    'pa_res_status' => 'U',
                    'signature_verification' => 'Y',
                    'eci_flag' => '07',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => 'no zero'
                ],
            ],
            'Centinel case 4,5 no params' => [false, false, []]
        ];
    }

    /**
     * Data for attempts performed processing
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getProcessingAttemptsPerformedData()
    {
        return [
            'Centinel case 11' => [
                true,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 pa_res_status is absent' => [
                false,
                true,
                [
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 signature_verification is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 eci_flag is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 xid is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 cavv is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 error_no is absent' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string'
                ],
            ],
            'Centinel case no params' => [false, true, []],
            'Centinel case 11 wrong pa_res_status' => [
                false,
                true,
                [
                    'pa_res_status' => 'wrong value',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 wrong signature_verification' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'wrong value',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 wrong eci_flag' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => 'wrong value',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 empty xid' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => '',
                    'cavv' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 empty cavv' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => '',
                    'error_no' => '0'
                ],
            ],
            'Centinel case 11 no zero' => [
                false,
                true,
                [
                    'pa_res_status' => 'A',
                    'signature_verification' => 'Y',
                    'eci_flag' => '06',
                    'xid' => 'some string',
                    'cavv' => 'some string',
                    'error_no' => 'no zero'
                ],
            ]
        ];
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
        return [
            'Centinel case 5' => [
                true,
                false,
                ['enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 5 enrolled is absent' => [
                false,
                false,
                ['acs_url' => '', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 5 acs_url is absent' => [
                false,
                false,
                ['enrolled' => '', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 5 payload is absent' => [
                false,
                false,
                ['enrolled' => '', 'acs_url' => '', 'error_no' => '0'],
            ],
            'Centinel case 5 error_no is absent' => [
                false,
                false,
                ['enrolled' => '', 'acs_url' => '', 'payload' => ''],
            ],
            'Centinel case 5 no params' => [false, false, []],
            'Centinel case 5 not empty enrolled' => [
                false,
                false,
                ['enrolled' => 'not empty', 'acs_url' => '', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 5 not empty acs_url' => [
                false,
                false,
                ['enrolled' => '', 'acs_url' => 'not empty', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 5 not empty payload' => [
                false,
                false,
                ['enrolled' => '', 'acs_url' => '', 'payload' => 'not empty', 'error_no' => '0'],
            ],
            'Centinel case 5 no zero error_no' => [
                false,
                false,
                ['enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => 'not zero'],
            ],
            'Centinel case 5 empty error_no' => [
                false,
                false,
                ['enrolled' => '', 'acs_url' => '', 'payload' => '', 'error_no' => ''],
            ],
            'Centinel case 7' => [
                true,
                false,
                ['enrolled' => 'U', 'acs_url' => '', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 8,9' => [
                true,
                false,
                ['enrolled' => 'U', 'acs_url' => '', 'payload' => '', 'error_no' => 'some string'],
            ],
            'Centinel case 7,8,9 enrolled is absent' => [
                false,
                false,
                ['acs_url' => '', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 7,8,9 acs_url is absent' => [
                false,
                false,
                ['enrolled' => 'U', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 7,8,9 payload is absent' => [
                false,
                false,
                ['enrolled' => 'U', 'acs_url' => '', 'error_no' => '0'],
            ],
            'Centinel case 7,8,9 error_no no params' => [false, false, []],
            'Centinel case 7,8,9 wrong enrolled' => [
                false,
                false,
                ['enrolled' => 'wrong value', 'acs_url' => '', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 7,8,9 not empty acs_url' => [
                false,
                false,
                ['enrolled' => 'U', 'acs_url' => 'not empty', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel case 7,8,9 not empty payload' => [
                false,
                false,
                ['enrolled' => 'U', 'acs_url' => '', 'payload' => 'not empty', 'error_no' => '0'],
            ]
        ];
    }

    /**
     * Data for strict successful lookup
     *
     * @return array
     */
    protected function _getStrictSuccessfulLookupData()
    {
        return [
            'Centinel cases 1-4, 6, 10-11' => [
                true,
                true,
                ['enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => 'some string', 'error_no' => '0'],
            ],
            'Centinel cases 1-4, 6, 10-11 enrolled is absent' => [
                false,
                true,
                ['acs_url' => 'some string', 'payload' => 'some string', 'error_no' => '0'],
            ],
            'Centinel cases 1-4, 6, 10-11 acs_url is absent' => [
                false,
                true,
                ['enrolled' => 'Y', 'payload' => 'some string', 'error_no' => '0'],
            ],
            'Centinel cases 1-4, 6, 10-11 payload is absent' => [
                false,
                true,
                ['enrolled' => 'Y', 'acs_url' => 'some string', 'error_no' => '0'],
            ],
            'Centinel cases 1-4, 6, 10-11 error_no is absent' => [
                false,
                true,
                ['enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => 'some string'],
            ],
            'Centinel cases 1-4, 6, 10-11 no params' => [false, true, []],
            'Centinel cases 1-4, 6, 10-11 wrong enrolled' => [
                false,
                true,
                [
                    'enrolled' => 'wrong value',
                    'acs_url' => 'some string',
                    'payload' => 'some string',
                    'error_no' => '0'
                ],
            ],
            'Centinel cases 1-4, 6, 10-11 empty enrolled' => [
                false,
                true,
                ['enrolled' => '', 'acs_url' => 'some string', 'payload' => 'some string', 'error_no' => '0'],
            ],
            'Centinel cases 1-4, 6, 10-11 empty acs_url' => [
                false,
                true,
                ['enrolled' => 'Y', 'acs_url' => '', 'payload' => 'some string', 'error_no' => '0'],
            ],
            'Centinel cases 1-4, 6, 10-11 empty payload' => [
                false,
                true,
                ['enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => '', 'error_no' => '0'],
            ],
            'Centinel cases 1-4, 6, 10-11 wrong error_no' => [
                false,
                true,
                [
                    'enrolled' => 'Y',
                    'acs_url' => 'some string',
                    'payload' => 'some string',
                    'error_no' => 'wrong value'
                ],
            ],
            'Centinel cases 1-4, 6, 10-11 empty error_no' => [
                false,
                true,
                ['enrolled' => 'Y', 'acs_url' => 'some string', 'payload' => 'some string', 'error_no' => ''],
            ]
        ];
    }
}
