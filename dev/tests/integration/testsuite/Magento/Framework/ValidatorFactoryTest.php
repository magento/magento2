<?php
/**
 * Integration test for Magento\Framework\ValidatorFactory
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
namespace Magento\Framework;

class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Framework\ValidatorFactory');
    }

    public function testCreateWithInstanceName()
    {
        $setName = 'Magento\Framework\Object';
        $this->assertInstanceOf($setName, $this->model->create([], $setName));
    }

    public function testCreateDefault()
    {
        $default = 'Magento\Framework\Validator';
        $this->assertInstanceOf($default, $this->model->create());
    }
}