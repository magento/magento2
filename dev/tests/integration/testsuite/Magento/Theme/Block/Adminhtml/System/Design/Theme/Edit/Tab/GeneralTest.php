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
}
