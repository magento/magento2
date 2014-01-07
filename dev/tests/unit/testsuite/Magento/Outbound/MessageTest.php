<?php
/**
 * \Magento\Outbound\Message
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
 * @package     Magento_Outbound
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Outbound;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function test() 
    {
        $uut = new \Magento\Outbound\Message('http://localhost', array('key1'=>'val1', 'key2' => 'val2'), "Body");
        // check endpoint url
        $this->assertSame('http://localhost', $uut->getEndpointUrl());
        // check headers
        $rsltHdr = $uut->getHeaders();
        $this->assertSame('val1', $rsltHdr['key1']);
        $this->assertSame('val2', $rsltHdr['key2']);
        // check for body
        $this->assertSame("Body", $uut->getBody());
        // check for default timeout
        $this->assertSame(20, $uut->getTimeout());
    }
}
