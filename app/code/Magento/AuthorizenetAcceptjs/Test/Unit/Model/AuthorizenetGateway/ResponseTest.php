<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Model\AuthorizenetGateway;

use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\Response;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testToApiXmlConvertsDataCorrectly()
    {
        $response = new Response();

        $data = '<foobar>'
            . '<level1>abc</level1>'
            . '<badchars>&lt;&gt;\'&quot;&amp;</badchars>'
            . '<twolevels><level2>def</level2></twolevels>'
            . '<threelevels><level2><level3>ghi</level3></level2></threelevels>'
            . '</foobar>';

        $response->hydrateWithXml($data);

        $this->assertSame('foobar', $response->getData(Response::RESPONSE_TYPE));
        $this->assertSame('abc', $response->getData('level1'));
        $this->assertSame('<>\'"&', $response->getData('badchars'));
        $this->assertSame(['level2'=>'def'], $response->getData('twolevels'));
        $this->assertSame(['level2'=>['level3'=>'ghi']], $response->getData('threelevels'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Invalid response type.
     */
    public function testHydrateWithXmlThrowsExceptionWhenInvalid()
    {
        $response = new Response();
        $response->hydrateWithXml('');
    }
}
