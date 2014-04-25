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
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute'
        );
    }

    public function testAddPrice()
    {
        $this->assertEmpty($this->_model->getPrices());
        $this->_model->addPrice(100);
        $this->assertEquals(array(100), $this->_model->getPrices());
    }

    public function testGetLabel()
    {
        $this->assertEmpty($this->_model->getLabel());
        $this->_model->setProductAttribute(new \Magento\Framework\Object(array('store_label' => 'Store Label')));
        $this->assertEquals('Store Label', $this->_model->getLabel());

        $this->_model->setUseDefault(
            1
        )->setProductAttribute(
            new \Magento\Framework\Object(array('store_label' => 'Other Label'))
        );
        $this->assertEquals('Other Label', $this->_model->getLabel());
    }
}
