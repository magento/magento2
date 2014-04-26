<?php
/**
 * Test \Magento\Webapi\Model\Soap\Wsdl\Factory
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap\Wsdl;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Webapi\Model\Soap\Wsdl\Factory */
    protected $_soapWsdlFactory;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMockBuilder(
            'Magento\Framework\ObjectManager'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMockForAbstractClass();
        $this->_soapWsdlFactory = new \Magento\Webapi\Model\Soap\Wsdl\Factory($this->_objectManagerMock);
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_objectManagerMock);
        unset($this->_soapWsdlFactory);
        parent::tearDown();
    }

    public function testCreate()
    {
        $wsdlName = 'wsdlName';
        $endpointUrl = 'endpointUrl';
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Webapi\Model\Soap\Wsdl',
            array('name' => $wsdlName, 'uri' => $endpointUrl)
        );
        $this->_soapWsdlFactory->create($wsdlName, $endpointUrl);
    }
}
