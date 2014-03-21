<?php
/**
 * \Magento\Customer\Model\Resource\Customer\Collection
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
