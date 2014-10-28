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
namespace Magento\Framework\DB;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\DB\Transaction');
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testSaveDelete()
    {
        $first = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $first->setData(
            array('website_id' => 1, 'name' => 'test 1', 'root_category_id' => 1, 'default_store_id' => 1)
        );

        $second = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $second->setData(
            array('website_id' => 1, 'name' => 'test 2', 'root_category_id' => 1, 'default_store_id' => 1)
        );


        $first->save();
        $this->_model->addObject($first)->addObject($second, 'second');
        $this->_model->save();
        $this->assertNotEmpty($first->getId());
        $this->assertNotEmpty($second->getId());

        $this->_model->delete();

        $test = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $test->load($first->getId());
        $this->assertEmpty($test->getId());
    }
}
