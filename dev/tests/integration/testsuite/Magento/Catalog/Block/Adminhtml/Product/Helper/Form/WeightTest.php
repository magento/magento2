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
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

class WeightTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_formFactory = $this->_objectManager->create('Magento\Framework\Data\FormFactory');
    }

    /**
     * @param string $type
     * @dataProvider virtualTypesDataProvider
     */
    public function testIsVirtualChecked($type)
    {
        /** @var $currentProduct \Magento\Catalog\Model\Product */
        $currentProduct = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $currentProduct->setTypeInstance($this->_objectManager->create($type));
        /** @var $block \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight */
        $block = $this->_objectManager->create('Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight');
        $form = $this->_formFactory->create();
        $form->setDataObject($currentProduct);
        $block->setForm($form);

        $this->assertContains(
            'checked="checked"',
            $block->getElementHtml(),
            'Is Virtual checkbox is not selected for virtual products'
        );
    }

    /**
     * @return array
     */
    public static function virtualTypesDataProvider()
    {
        return array(
            array('Magento\Catalog\Model\Product\Type\Virtual'),
            array('Magento\Downloadable\Model\Product\Type')
        );
    }

    /**
     * @param string $type
     * @dataProvider physicalTypesDataProvider
     */
    public function testIsVirtualUnchecked($type)
    {
        /** @var $currentProduct \Magento\Catalog\Model\Product */
        $currentProduct = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $currentProduct->setTypeInstance($this->_objectManager->create($type));

        /** @var $block \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight */
        $block = $this->_objectManager->create('Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight');
        $form = $this->_formFactory->create();
        $form->setDataObject($currentProduct);
        $block->setForm($form);

        $this->assertNotContains(
            'checked="checked"',
            $block->getElementHtml(),
            'Is Virtual checkbox is selected for physical products'
        );
    }

    /**
     * @return array
     */
    public static function physicalTypesDataProvider()
    {
        return array(array('Magento\Catalog\Model\Product\Type\Simple'), array('Magento\Bundle\Model\Product\Type'));
    }
}
