<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction;

use Magento\Customer\Block\Adminhtml\Grid\Renderer\AbstractMultiactionTest;
use Magento\Framework\Module\Manager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class checks multiaction block rendering with bundle product.
 *
 * @see \Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction
 */
class MultiactionBundleTest extends AbstractMultiactionTest
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Manager $moduleManager */
        $moduleManager = $objectManager->get(Manager::class);
        //This check is needed because Customer independent of Magento_Bundle
        if (!$moduleManager->isEnabled('Magento_Bundle')) {
            self::markTestSkipped('Magento_Bundle module disabled.');
        }
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/quote_with_bundle_and_options.php
     * @return void
     */
    public function testRenderConfigurableProduct(): void
    {
        $this->processRender();
    }
}
