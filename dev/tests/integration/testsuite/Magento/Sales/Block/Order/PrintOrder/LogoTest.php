<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\PrintOrder;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\ViewModel\Header\LogoPathResolver as LogoPathResolverSales;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Theme\ViewModel\Block\Html\Header\LogoPathResolver as LogoPathResolverDefault;
use PHPUnit\Framework\TestCase;

class LogoTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->objectManager->get(State::class)
            ->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
    }

    /**
     * @magentoConfigFixture default_store design/header/logo_src default/logo.jpg
     * @magentoConfigFixture default_store sales/identity/logo_html default/logo_sales.jpg
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testGetLogoSrc(): void
    {
        $host = 'http://localhost/media/';
        $defaultLogoFile= 'logo.jpg';
        $defaultPath = 'logo/default/' . $defaultLogoFile;
        $salesLogoFile = 'logo_sales.jpg';
        $salesPath = 'sales/store/logo_html/default/' . $salesLogoFile;
        $this->mediaDirectory->writeFile($defaultPath, '');
        $this->mediaDirectory->writeFile($salesPath, '');
        $blockArguments = ['data' =>
            ['logoPathResolver' => $this->objectManager->get(LogoPathResolverDefault::class)]
        ];
        /** @var Logo $block */
        $block = $this->objectManager->create(LayoutInterface::class)
            ->createBlock(Logo::class, 'logo', $blockArguments);
        $this->assertSame($host . $defaultPath, $block->getLogoSrc());
        $blockArguments = ['data' =>
            ['logoPathResolver' => $this->objectManager->get(LogoPathResolverSales::class)]
        ];
        /** @var Logo $block */
        $block = $this->objectManager->create(LayoutInterface::class)
            ->createBlock(Logo::class, 'logo', $blockArguments);
        $this->assertSame($host . $salesPath, $block->getLogoSrc());
        $this->mediaDirectory->delete($defaultPath);
        $this->mediaDirectory->delete($salesPath);
    }

    /**
     * Checks that fallback to header logo works fine
     *
     * @magentoConfigFixture default_store design/header/logo_src default/logo.jpg
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testGetLogoSrcWithFallback(): void
    {
        $host = 'http://localhost/media/';
        $defaultLogoFile = 'logo.jpg';
        $defaultPath = 'logo/default/' . $defaultLogoFile;
        $this->mediaDirectory->writeFile($defaultPath, '');
        $blockArguments = ['data' =>
            ['logoPathResolver' => $this->objectManager->get(LogoPathResolverDefault::class)]
        ];
        /** @var Logo $block */
        $block = $this->objectManager->create(LayoutInterface::class)
            ->createBlock(Logo::class, 'logo', $blockArguments);
        $this->assertSame($host . $defaultPath, $block->getLogoSrc());
        $blockArguments = ['data' =>
            ['logoPathResolver' => $this->objectManager->get(LogoPathResolverSales::class)]
        ];
        /** @var Logo $block */
        $block = $this->objectManager->create(LayoutInterface::class)
            ->createBlock(Logo::class, 'logo', $blockArguments);
        $this->assertSame($host . $defaultPath, $block->getLogoSrc());
        $this->mediaDirectory->delete($defaultPath);
    }
}
