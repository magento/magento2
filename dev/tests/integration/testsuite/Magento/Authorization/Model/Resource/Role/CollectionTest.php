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
namespace Magento\Authorization\Model\Resource\Role;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Role collection test
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorization\Model\Resource\Role\Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Authorization\Model\Resource\Role\Collection'
        );
    }

    public function testSetUserFilter()
    {
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\User\Model\User');
        $user->loadByUsername(\Magento\TestFramework\Bootstrap::ADMIN_NAME);
        $this->_collection->setUserFilter($user->getId(), UserContextInterface::USER_TYPE_ADMIN);

        $selectQueryStr = $this->_collection->getSelect()->__toString();

        $this->assertContains('user_id', $selectQueryStr);
        $this->assertContains('user_type', $selectQueryStr);
    }

    public function testSetRolesFilter()
    {
        $this->_collection->setRolesFilter();

        $this->assertContains('role_type', $this->_collection->getSelect()->__toString());
    }

    public function testToOptionArray()
    {
        $this->assertNotEmpty($this->_collection->toOptionArray());

        foreach ($this->_collection->toOptionArray() as $item) {
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('label', $item);
        }
    }
}
