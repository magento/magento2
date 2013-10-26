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

namespace Magento\Adminhtml\Block\Catalog\Product\Edit;

/**
 * @magentoAppArea adminhtml
 */
class TabsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testPrepareLayout()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_ADMINHTML)
            ->setDefaultDesignTheme();
        $objectManager->get('Magento\Config\ScopeInterface')
            ->setCurrentScope(\Magento\Core\Model\App\Area::AREA_ADMINHTML);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $product->load(1); // fixture
        $objectManager->get('Magento\Core\Model\Registry')->register('product', $product);

        /** @var $layout \Magento\Core\Model\Layout */
        $layout = $objectManager->get('Magento\View\LayoutInterface');
        $layout->addBlock('Magento\Core\Block\Text', 'head');
        $layout->setArea('nonexisting'); // prevent block templates rendering
        /** @var $block \Magento\Adminhtml\Block\Catalog\Product\Edit\Tabs */
        $block = $layout->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tabs');
        $this->assertArrayHasKey(0, $block->getTabsIds());
        $this->assertNotEmpty($layout->getBlock('catalog\product\edit\tabs'));
    }
}
