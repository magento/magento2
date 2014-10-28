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

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Backend
 */
namespace Magento\Eav\Model\Validator\Attribute;

class BackendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Eav\Model\Validator\Attribute\Backend();
    }

    /**
     * Test method for \Magento\Eav\Model\Validator\Attribute\Backend::isValid
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsValid()
    {
        /** @var $entity \Magento\Customer\Model\Customer */
        $entity = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Customer'
        )->load(
            1
        );

        $this->assertTrue($this->_model->isValid($entity));
        $this->assertEmpty($this->_model->getMessages());

        $entity->setData('email', null);
        $this->assertFalse($this->_model->isValid($entity));
        $this->assertArrayHasKey('email', $this->_model->getMessages());

        $entity->setData('firstname', null);
        $this->assertFalse($this->_model->isValid($entity));
        $this->assertArrayHasKey('email', $this->_model->getMessages());
        $this->assertArrayHasKey('firstname', $this->_model->getMessages());
    }
}
