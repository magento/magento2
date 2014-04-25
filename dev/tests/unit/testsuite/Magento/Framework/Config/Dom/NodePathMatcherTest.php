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
namespace Magento\Framework\Config\Dom;

class NodePathMatcherTest extends \PHPUnit_Framework_TestCase
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
        return array(
            'no match' => array('/root/node', '/root', false),
            'partial match' => array('/root/node', '/wrapper/root/node', false),
            'exact match' => array('/root/node', '/root/node', true),
            'regexp match' => array('/root/node/(sub-)+node', '/root/node/sub-node', true),
            'match with namespace' => array('/root/node', '/mage:root/node', true),
            'match with predicate' => array('/root/node', '/root/node[@name="test"]', true)
        );
    }
}
