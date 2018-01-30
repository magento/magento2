<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class GeneralTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\LayoutInterface */
    protected $_layout;

    /** @var \Magento\Framework\View\Design\ThemeInterface */
    protected $_theme;

    /** @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab_General */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $this->_theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $this->_theme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);
        $this->_block = $this->_layout->createBlock(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\General'
        );
    }

    public function testToHtmlPreviewImageNote()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_theme', $this->_theme);
        $this->_block->setArea('adminhtml');

        $this->_block->toHtml();

        $noticeText = $this->_block->getForm()->getElement('preview_image')->getNote();
        $this->assertNotEmpty($noticeText);
    }

    public function testToHtmlPreviewImageUrl()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $this->_theme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL);
        $this->_theme->setPreviewImage('preview_image_test.jpg');
        $this->_block->setArea('adminhtml');

        $html = $this->_block->toHtml();
        preg_match_all('/pub\/static\/adminhtml\/_view\/en_US/', $html, $result);
        $this->assertEmpty($result[0]);
    }
}
