<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Model\AuthorizenetGateway;

use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\Request;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testToApiXmlConvertsDataCorrectly()
    {
        $request = new Request();
        $request->setData(Request::REQUEST_TYPE, 'foobar');
        $request->setData('level1', 'abc');
        $request->setData('badchars', '<>\'"&');
        $request->setData('twolevels', ['level2' => 'def']);
        $request->setData('threelevels', ['level2' => ['level3' => 'ghi']]);

        $expected = '<foobar>'
            . '<level1>abc</level1>'
            . '<badchars>&lt;&gt;\'&quot;&amp;</badchars>'
            . '<twolevels><level2>def</level2></twolevels>'
            . '<threelevels><level2><level3>ghi</level3></level2></threelevels>'
            . '</foobar>';
        $actual = $request->toApiXml();
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Invalid request type.
     */
    public function testToApiXmlThrowsExceptionWhenInvalid()
    {
        $request = new Request();
        $request->setData('level1', 'abc');
        $request->toApiXml();
    }
}
