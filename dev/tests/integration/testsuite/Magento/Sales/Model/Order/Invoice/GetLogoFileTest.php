<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Invoice;

use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Framework\App\Config\MutableScopeConfigInterface;

/**
 * Test class for \Magento\Sales\Model\Order\Invoice\GetLogoFile
 */
class GetLogoFileTest extends \PHPUnit\Framework\TestCase
{
    private const XML_PATH_SALES_IDENTITY_LOGO_HTML = 'sales/identity/logo_html';
    private const DUMP_IMAGE = 'my_dump_logo.png';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MutableScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GetLogoFile
     */
    private $getLogoFile;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        $this->getLogoFile = $this->objectManager->get(GetLogoFile::class);
    }

    /**
     * Check that GetLogoFile return image after Admin configuration is changed
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->assertNull($this->getLogoFile->execute());

        $this->applyImage();

        $this->assertIsString($this->getLogoFile->execute());
        $this->assertStringContainsString(self::DUMP_IMAGE, $this->getLogoFile->execute());
    }

    /**
     * Set Invoice Custom Logo HTML Image configuration
     *
     * @return void
     */
    private function applyImage(): void
    {
        $this->scopeConfig->setValue(
            self::XML_PATH_SALES_IDENTITY_LOGO_HTML,
            self::DUMP_IMAGE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
