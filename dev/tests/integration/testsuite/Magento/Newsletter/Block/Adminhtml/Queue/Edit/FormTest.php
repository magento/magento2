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
namespace Magento\Newsletter\Block\Adminhtml\Queue\Edit;

/**
 * Test class for \Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $queue = $objectManager->get('Magento\Newsletter\Model\Queue');
        /** @var \Magento\Framework\Registry $registry */
        $registry = $objectManager->get('\Magento\Framework\Registry');
        $registry->register('current_queue', $queue);

        $objectManager->get(
            'Magento\Framework\View\DesignInterface'
        )->setArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->setDefaultDesignTheme();
        $objectManager->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $block = $objectManager->create(
            'Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form',
            array('registry' => $registry)
        );
        $prepareFormMethod = new \ReflectionMethod(
            'Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form',
            '_prepareForm'
        );
        $prepareFormMethod->setAccessible(true);

        $statuses = array(
            \Magento\Newsletter\Model\Queue::STATUS_NEVER,
            \Magento\Newsletter\Model\Queue::STATUS_PAUSE
        );
        foreach ($statuses as $status) {
            $queue->setQueueStatus($status);
            $prepareFormMethod->invoke($block);
            $element = $block->getForm()->getElement('date');
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getTimeFormat());
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
