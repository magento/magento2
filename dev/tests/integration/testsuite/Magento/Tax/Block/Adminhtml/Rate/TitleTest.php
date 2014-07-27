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
namespace Magento\Tax\Block\Adminhtml\Rate;

use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Controller\RegistryConstants;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Block\Adminhtml\Rate\Title
     */
    protected $_block;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testGetTitles()
    {
        /** @var \Magento\Tax\Model\Calculation\Rate $rate */
        $rate = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rate');
        $rate->load(1);
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_objectManager->get('Magento\Store\Model\Store');
        $store->load('test', 'code');
        $title = 'title';
        $rate->saveTitles([$store->getId() => $title]);

        $coreRegistry = $this->_objectManager->create('Magento\Framework\Registry');
        $coreRegistry->register(RegistryConstants::CURRENT_TAX_RATE_ID, 1);

        /** @var \Magento\Tax\Block\Adminhtml\Rate\Title $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Tax\Block\Adminhtml\Rate\Title',
            [
                'coreRegistry' => $coreRegistry,
            ]
        );
        $titles = $block->getTitles();
        $this->assertArrayHasKey($store->getId(), $titles, 'Store was not created');
        $this->assertEquals($title, $titles[$store->getId()], 'Invalid Tax Title');
    }
}
