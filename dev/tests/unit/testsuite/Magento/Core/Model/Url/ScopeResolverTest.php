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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Url;

class ScopeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')->getMock();
        $this->_object = $objectManager->getObject(
            'Magento\Core\Model\Url\ScopeResolver',
            array('storeManager' => $this->_storeManagerMock)
        );
    }

    /**
     * @dataProvider getScopeDataProvider
     * @param int|null$scopeId
     */
    public function testGetScope($scopeId)
    {
        $scopeMock = $this->getMockBuilder('\Magento\Framework\Url\ScopeInterface')->getMock();
        $this->_storeManagerMock->expects(
            $this->at(0)
        )->method(
            'getStore'
        )->with(
            $scopeId
        )->will(
            $this->returnValue($scopeMock)
        );
        $this->_object->getScope($scopeId);
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Invalid scope object
     */
    public function testGetScopeException()
    {
        $this->_object->getScope();
    }

    /**
     * @return array
     */
    public function getScopeDataProvider()
    {
        return array(array(null), array(1));
    }

    public function testGetScopes()
    {
        $this->_storeManagerMock->expects($this->once())->method('getStores');
        $this->_object->getScopes();
    }
}
