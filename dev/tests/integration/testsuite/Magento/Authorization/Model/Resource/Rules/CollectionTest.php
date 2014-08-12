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
namespace Magento\Authorization\Model\Resource\Rules;

/**
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorization\Model\Resource\Rules\Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Authorization\Model\Resource\Rules\Collection'
        );
    }

    public function testGetByRoles()
    {
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\User\Model\User');
        $user->loadByUsername(\Magento\TestFramework\Bootstrap::ADMIN_NAME);
        $this->_collection->getByRoles($user->getRole()->getId());

        $where = $this->_collection->getSelect()->getPart(\Zend_Db_Select::WHERE);
        /** @var \Zend_Db_Adapter_Abstract $adapter */
        $adapter = $this->_collection->getConnection();
        $quote = $adapter->getQuoteIdentifierSymbol();
        $this->assertContains("({$quote}role_id{$quote} = '" . $user->getRole()->getId() . "')", $where);
    }

    public function testAddSortByLength()
    {
        $this->_collection->addSortByLength();

        $order = $this->_collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
        $this->assertContains(array('length', 'DESC'), $order);
    }
}
