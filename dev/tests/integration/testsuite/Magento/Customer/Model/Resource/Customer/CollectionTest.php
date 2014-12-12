<?php
/**
 * \Magento\Customer\Model\Resource\Customer\Collection
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Resource\Customer;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Resource\Customer\Collection
     */
    protected $_collection;

    public function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Customer\Collection'
        );
    }

    public function testAddNameToSelect()
    {
        $this->_collection->addNameToSelect();
        $joinParts = $this->_collection->getSelect()->getPart(\Zend_Db_Select::FROM);

        $this->assertArrayHasKey('at_prefix', $joinParts);
        $this->assertArrayHasKey('at_firstname', $joinParts);
        $this->assertArrayHasKey('at_middlename', $joinParts);
        $this->assertArrayHasKey('at_lastname', $joinParts);
        $this->assertArrayHasKey('at_suffix', $joinParts);
    }
}
