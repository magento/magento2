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
namespace Magento\Sales\Service\V1\Data;

class OrderAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAddressType()
    {
        $data = ['address_type' => 'test_value_address_type'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_address_type', $object->getAddressType());
    }

    public function testGetCity()
    {
        $data = ['city' => 'test_value_city'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_city', $object->getCity());
    }

    public function testGetCompany()
    {
        $data = ['company' => 'test_value_company'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_company', $object->getCompany());
    }

    public function testGetCountryId()
    {
        $data = ['country_id' => 'test_value_country_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_country_id', $object->getCountryId());
    }

    public function testGetCustomerAddressId()
    {
        $data = ['customer_address_id' => 'test_value_customer_address_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_customer_address_id', $object->getCustomerAddressId());
    }

    public function testGetCustomerId()
    {
        $data = ['customer_id' => 'test_value_customer_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_customer_id', $object->getCustomerId());
    }

    public function testGetEmail()
    {
        $data = ['email' => 'test_value_email'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_email', $object->getEmail());
    }

    public function testGetEntityId()
    {
        $data = ['entity_id' => 'test_value_entity_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_entity_id', $object->getEntityId());
    }

    public function testGetFax()
    {
        $data = ['fax' => 'test_value_fax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_fax', $object->getFax());
    }

    public function testGetFirstname()
    {
        $data = ['firstname' => 'test_value_firstname'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_firstname', $object->getFirstname());
    }

    public function testGetLastname()
    {
        $data = ['lastname' => 'test_value_lastname'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_lastname', $object->getLastname());
    }

    public function testGetMiddlename()
    {
        $data = ['middlename' => 'test_value_middlename'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_middlename', $object->getMiddlename());
    }

    public function testGetParentId()
    {
        $data = ['parent_id' => 'test_value_parent_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_parent_id', $object->getParentId());
    }

    public function testGetPostcode()
    {
        $data = ['postcode' => 'test_value_postcode'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_postcode', $object->getPostcode());
    }

    public function testGetPrefix()
    {
        $data = ['prefix' => 'test_value_prefix'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_prefix', $object->getPrefix());
    }

    public function testGetQuoteAddressId()
    {
        $data = ['quote_address_id' => 'test_value_quote_address_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_quote_address_id', $object->getQuoteAddressId());
    }

    public function testGetRegion()
    {
        $data = ['region' => 'test_value_region'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_region', $object->getRegion());
    }

    public function testGetRegionId()
    {
        $data = ['region_id' => 'test_value_region_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_region_id', $object->getRegionId());
    }

    public function testGetStreet()
    {
        $data = ['street' => 'test_value_street'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_street', $object->getStreet());
    }

    public function testGetSuffix()
    {
        $data = ['suffix' => 'test_value_suffix'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_suffix', $object->getSuffix());
    }

    public function testGetTelephone()
    {
        $data = ['telephone' => 'test_value_telephone'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_telephone', $object->getTelephone());
    }

    public function testGetVatId()
    {
        $data = ['vat_id' => 'test_value_vat_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_vat_id', $object->getVatId());
    }

    public function testGetVatIsValid()
    {
        $data = ['vat_is_valid' => 'test_value_vat_is_valid'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_vat_is_valid', $object->getVatIsValid());
    }

    public function testGetVatRequestDate()
    {
        $data = ['vat_request_date' => 'test_value_vat_request_date'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_vat_request_date', $object->getVatRequestDate());
    }

    public function testGetVatRequestId()
    {
        $data = ['vat_request_id' => 'test_value_vat_request_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_vat_request_id', $object->getVatRequestId());
    }

    public function testGetVatRequestSuccess()
    {
        $data = ['vat_request_success' => 'test_value_vat_request_success'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderAddress($abstractBuilderMock);

        $this->assertEquals('test_value_vat_request_success', $object->getVatRequestSuccess());
    }
}
