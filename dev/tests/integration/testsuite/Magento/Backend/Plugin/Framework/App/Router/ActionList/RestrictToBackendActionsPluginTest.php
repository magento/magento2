<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Plugin\Framework\App\Router\ActionList;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class RestrictToBackendActionsPluginTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @return void
     */
    public function testBackendActionIsResolved(): void
    {
        $this->assertSame(
            'Magento\Tax\Controller\Adminhtml\Rule\AjaxLoadRates',
            $this->objectManager->get(ActionList::class)->get('Magento_Tax', 'adminhtml', 'rule', 'ajaxLoadRates')
        );
    }

    /**
     * @return void
     */
    public function testAllowedNonBackendActionIsResolved(): void
    {
        $this->assertSame(
            'Magento\Paypal\Controller\Adminhtml\Transparent\Redirect',
            $this->objectManager->get(ActionList::class)->get('Magento_Paypal', 'adminhtml', 'transparent', 'redirect')
        );
    }

    /**
     * @return void
     */
    public function testNonBackendActionIsDropped(): void
    {
        $this->assertNull(
            $this->objectManager->get(RestrictToBackendActionsPlugin::class)->afterGet(
                $this->objectManager->get(ActionList::class),
                Forward::class,
                'Magento_Backend',
                'adminhtml'
            )
        );
    }
}
