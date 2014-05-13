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
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testPackageThemeElement()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')
            ->register('current_widget_instance', new \Magento\Framework\Object());
        /** @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main'
        );
        $block->setTemplate(null);
        $block->toHtml();
        $element = $block->getForm()->getElement('theme_id');
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Select', $element);
        $this->assertTrue($element->getDisabled());
    }

    public function testTypeElement()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Layout'
        )->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main'
        );
        $block->setTemplate(null);
        $block->toHtml();
        $element = $block->getForm()->getElement('instance_code');
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Select', $element);
        $this->assertTrue($element->getDisabled());
    }
}
