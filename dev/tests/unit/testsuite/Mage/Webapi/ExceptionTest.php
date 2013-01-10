<?php
/**
 * Test Webapi module exception.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_ExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test Webapi exception construct.
     */
    public function testConstruct()
    {
        $apiException = new Mage_Webapi_Exception('Message', Mage_Webapi_Exception::HTTP_UNAUTHORIZED);
        /** Assert the set Exception code. */
        $this->assertEquals(
            $apiException->getCode(),
            Mage_Webapi_Exception::HTTP_UNAUTHORIZED,
            'Exception code is set incorrectly in construct.'
        );
        /** Assert the set Exception message. */
        $this->assertEquals(
            $apiException->getMessage(),
            'Message',
            'Exception message is set incorrectly in construct.'
        );
    }

    /**
     * Test Webapi exception construct with invalid data.
     *
     * @dataProvider providerForTestConstructInvalidCode
     */
    public function testConstructInvalidCode($code)
    {
        $this->setExpectedException('InvalidArgumentException', 'The specified code "' . $code . '" is invalid.');
        /** Create Mage_Webapi_Exception object with invalid code. */
        /** Valid codes range is from 400 to 599. */
        new Mage_Webapi_Exception('Message', $code);
    }

    public function testGetOriginatorSender()
    {
        $apiException = new Mage_Webapi_Exception('Message', Mage_Webapi_Exception::HTTP_UNAUTHORIZED);
        /** Check that Webapi Exception object with code 401 matches Sender originator.*/
        $this->assertEquals(
            Mage_Webapi_Exception::ORIGINATOR_SENDER,
            $apiException->getOriginator(),
            'Wrong Sender originator detecting.'
        );
    }

    public function testGetOriginatorReceiver()
    {
        $apiException = new Mage_Webapi_Exception('Message', Mage_Webapi_Exception::HTTP_INTERNAL_ERROR);
        /** Check that Webapi Exception object with code 500 matches Receiver originator.*/
        $this->assertEquals(
            Mage_Webapi_Exception::ORIGINATOR_RECEIVER,
            $apiException->getOriginator(),
            'Wrong Receiver originator detecting.'
        );
    }

    /**
     * Data provider for testConstructInvalidCode.
     *
     * @return array
     */
    public function providerForTestConstructInvalidCode()
    {
        return array(
            //Each array contains invalid Exception code.
            array(300),
            array(600),
        );
    }
}
