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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Adminhtml\Block\Newsletter\Queue\Edit;

/**
 * Test class for \Magento\Adminhtml\Block\Newsletter\Queue\Edit\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_ADMINHTML)
            ->setDefaultDesignTheme();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Config\ScopeInterface')
            ->setCurrentScope(\Magento\Core\Model\App\Area::AREA_ADMINHTML);
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Adminhtml\Block\Newsletter\Queue\Edit\Form');
        $prepareFormMethod = new \ReflectionMethod(
            'Magento\Adminhtml\Block\Newsletter\Queue\Edit\Form', '_prepareForm');
        $prepareFormMethod->setAccessible(true);

        $queue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Newsletter\Model\Queue');
        $statuses = array(\Magento\Newsletter\Model\Queue::STATUS_NEVER, \Magento\Newsletter\Model\Queue::STATUS_PAUSE);
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
