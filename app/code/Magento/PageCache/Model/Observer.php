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
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\PageCache\Model;

/**
 * Class Observer
 * @package Magento\PageCache\Model
 */
class Observer
{
    /**
     * Add comment cache containers to private blocks
     * Blocks are wrapped only if page is cacheable
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function processLayoutRenderElement(\Magento\Event\Observer $observer)
    {
        /** @var \Magento\Core\Model\Layout $layout */
        $layout = $observer->getEvent()->getLayout();
        if ($layout->isCacheable()) {
            $name = $observer->getEvent()->getElementName();
            $block = $layout->getBlock($name);
            if ($block instanceof \Magento\View\Element\AbstractBlock && $block->isScopePrivate()) {
                $transport = $observer->getEvent()->getTransport();
                $output = $transport->getData('output');
                $html = sprintf('<!-- BLOCK %1$s -->%2$s<!-- /BLOCK %1$s -->', $block->getNameInLayout(), $output);
                $transport->setData('output', $html);
            }
        }
    }
}
