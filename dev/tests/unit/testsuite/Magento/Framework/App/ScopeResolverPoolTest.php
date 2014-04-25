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

namespace Magento\Framework\App;

class ScopeResolverPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGet()
    {
        $scope = $this->getMock('\Magento\Framework\App\ScopeResolverInterface');
        $scopeResolver = $this->_helper->getObject('Magento\Framework\App\ScopeResolverPool', [
            'scopeResolvers' => [
                'test' => $scope
            ]
        ]);
        $this->assertSame($scope, $scopeResolver->get('test'));
    }

    /**
     * @param string $scope
     *
     * @covers \Magento\Framework\App\ScopeResolverPool::get()
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid scope type
     * @dataProvider testGetExceptionDataProvider
     */
    public function testGetException($scope)
    {
        $scopeResolver = $this->_helper->getObject('Magento\Framework\App\ScopeResolverPool', [
            'scopeResolvers' => [
                'test' => new \Magento\Framework\Object()
            ]
        ]);
        $scopeResolver->get($scope);
    }

    public function testGetExceptionDataProvider()
    {
        return [
            ['undefined'],
            ['test'],
        ];
    }
}