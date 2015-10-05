<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

class WeightTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
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
    public function testProductWithoutWeight($type)
    {
        /** @var $currentProduct \Magento\Catalog\Model\Product */
        $currentProduct = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $currentProduct->setTypeInstance($this->_objectManager->create($type));
        /** @var $block \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight */
        $block = $this->_objectManager->create('Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight');
        $form = $this->_formFactory->create();
        $form->setDataObject($currentProduct);
        $block->setForm($form);

        $this->assertRegExp(
            '/value="0".*checked="checked"/',
            $block->getElementHtml(),
            '"Does this have a weight" is set to "Yes" for virtual products'
        );
    }

    /**
     * @return array
     */
    public static function virtualTypesDataProvider()
    {
        return [
            ['Magento\Catalog\Model\Product\Type\Virtual'],
            ['Magento\Downloadable\Model\Product\Type']
        ];
    }

    /**
     * @param string $type
     * @dataProvider physicalTypesDataProvider
     */
    public function testProductHasWeight($type)
    {
        /** @var $currentProduct \Magento\Catalog\Model\Product */
        $currentProduct = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $currentProduct->setTypeInstance($this->_objectManager->create($type));

        /** @var $block \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight */
        $block = $this->_objectManager->create('Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight');
        $form = $this->_formFactory->create();
        $form->setDataObject($currentProduct);
        $block->setForm($form);
        $this->assertNotRegExp(
            '/value="0".*checked="checked"/',
            $block->getElementHtml(),
            '"Does this have a weight" is set to "No" for physical products'
        );
    }

    /**
     * @return array
     */
    public static function physicalTypesDataProvider()
    {
        return [['Magento\Catalog\Model\Product\Type\Simple'], ['Magento\Bundle\Model\Product\Type']];
    }
}
