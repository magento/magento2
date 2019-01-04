<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Model\AuthorizenetGateway;

use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\PayloadConverter;

class PayloadConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testConvertToXmlConvertsDataCorrectly()
    {
        $converter = new PayloadConverter();
        $data = [
            'level1' => 'abc',
            'badchars' => '<>\'"&',
            'twolevels' => ['level2' => 'def'],
            'threelevels' => ['level2' => ['level3' => 'ghi']],
            PayloadConverter::PAYLOAD_TYPE => 'foobar',
        ];

        $expected = '<foobar xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
            . '<level1>abc</level1>'
            . '<badchars>&lt;&gt;\'&quot;&amp;</badchars>'
            . '<twolevels><level2>def</level2></twolevels>'
            . '<threelevels><level2><level3>ghi</level3></level2></threelevels>'
            . '</foobar>';
        $actual = $converter->convertArrayToXml($data);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Invalid payload type.
     */
    public function testConvertToXmlThrowsExceptionWhenInvalid()
    {
        $converter = new PayloadConverter();
        $converter->convertArrayToXml(['level1' => 'abc']);
    }

    public function testXmlToArrayConvertsDataCorrectly()
    {
        $converter = new PayloadConverter();

        $xml = '<foobar>'
            . '<level1>abc</level1>'
            . '<badchars>&lt;&gt;\'&quot;&amp;</badchars>'
            . '<twolevels><level2>def</level2></twolevels>'
            . '<threelevels><level2><level3>ghi</level3></level2></threelevels>'
            . '<duplicates><dupe>abc</dupe><dupe>def</dupe></duplicates>'
            . '<duplicates2><dupe><foo>bar</foo></dupe><dupe><baz>bash</baz></dupe></duplicates2>'
            . '</foobar>';

        $actual = $converter->convertXmlToArray($xml);

        $expected = [
            'level1' => 'abc',
            'badchars' => '<>\'"&',
            'twolevels' => ['level2' => 'def'],
            'threelevels' => ['level2' => ['level3' => 'ghi']],
            'duplicates' => ['dupe' => ['abc','def']],
            'duplicates2' => ['dupe' => [['foo' => 'bar'],['baz' => 'bash']]],
            PayloadConverter::PAYLOAD_TYPE => 'foobar',
        ];
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Invalid payload type.
     */
    public function testXmlToArrayThrowsExceptionWhenInvalid()
    {
        $converter = new PayloadConverter();
        $converter->convertXmlToArray('');
    }
}
