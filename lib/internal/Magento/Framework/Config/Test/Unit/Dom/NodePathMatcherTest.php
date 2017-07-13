<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Dom;

use \Magento\Framework\Config\Dom\NodePathMatcher;

class NodePathMatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NodePathMatcher
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new NodePathMatcher();
    }

    /**
     * @param string $pathPattern
     * @param string $xpathSubject
     * @param boolean $expectedResult
     *
     * @dataProvider getNodeInfoDataProvider
     */
    public function testMatch($pathPattern, $xpathSubject, $expectedResult)
    {
        $actualResult = $this->_model->match($pathPattern, $xpathSubject);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function getNodeInfoDataProvider()
    {
        return [
            'no match' => ['/root/node', '/root', false],
            'partial match' => ['/root/node', '/wrapper/root/node', false],
            'exact match' => ['/root/node', '/root/node', true],
            'regexp match' => ['/root/node/(sub-)+node', '/root/node/sub-node', true],
            'match with namespace' => ['/root/node', '/mage:root/node', true],
            'match with predicate' => ['/root/node', '/root/node[@name="test"]', true]
        ];
    }
}
