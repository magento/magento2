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
namespace Magento\User\Block\Role\Tab;

/**
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Block\Role\Tab\Edit
     */
    protected $_block;

    protected function setUp()
    {
        $roleAdmin = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Authorization\Model\Role');
        $roleAdmin->load(\Magento\TestFramework\Bootstrap::ADMIN_ROLE_NAME, 'role_name');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\RequestInterface'
        )->setParam(
            'rid',
            $roleAdmin->getId()
        );

        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Block\Role\Tab\Edit'
        );
    }

    public function testConstructor()
    {
        $this->assertNotEmpty($this->_block->getSelectedResources());
        $this->assertContains('Magento_Adminhtml::all', $this->_block->getSelectedResources());
    }

    public function testGetTree()
    {
        $encodedTree = $this->_block->getTree();
        $this->assertNotEmpty($encodedTree);
    }
}
