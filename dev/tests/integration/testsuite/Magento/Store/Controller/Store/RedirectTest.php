<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Controller\Store;

use Magento\Framework\Session\SidResolverInterface;
use Magento\Store\Model\StoreResolver;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test Redirect controller.
 *
 * @magentoAppArea frontend
 */
class RedirectTest extends AbstractController
{
    /**
     * Check that there's no SID in redirect URL.
     *
     * @return void
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture current_store web/session/use_frontend_sid 1
     */
    public function testNoSid(): void
    {
        $this->getRequest()->setParam(StoreResolver::PARAM_NAME, 'fixture_second_store');
        $this->getRequest()->setParam('___from_store', 'test');

        $this->dispatch('/stores/store/redirect');

        $result = (string)$this->getResponse()->getHeader('location');
        $this->assertNotEmpty($result);
        $this->assertNotContains(SidResolverInterface::SESSION_ID_QUERY_PARAM .'=', $result);
    }
}
