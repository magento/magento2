<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Helper\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use stdClass;

class ImagesTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGetStorageRoot()
    {
        /** @var Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $mediaPath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        /** @var Images $helper */
        $helper = $this->objectManager->create(
            Images::class
        );
        $this->assertStringStartsWith($mediaPath, $helper->getStorageRoot());
    }

    /**
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     */
    public function testGetCurrentUrl()
    {
        /** @var Images $helper */
        $helper = $this->objectManager->create(Images::class);

        $this->assertStringStartsWith('http://example.com/', $helper->getCurrentUrl());
    }

    /**
     * @param bool $isStaticUrlsAllowed
     * @param string $filename
     * @param bool $renderAsTag
     * @param string|callable $expectedResult - string or callable to make unique assertions on $expectedResult
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     * @dataProvider providerGetImageHtmlDeclaration
     */
    public function testGetImageHtmlDeclaration(
        $isStaticUrlsAllowed,
        $filename,
        $renderAsTag,
        $expectedResult
    ) {
        $helper = $this->generateHelper($isStaticUrlsAllowed);

        $actualResult = $helper->getImageHtmlDeclaration($filename, $renderAsTag);

        if (is_callable($expectedResult)) {
            $expectedResult($actualResult);
        } else {
            $this->assertEquals(
                $expectedResult,
                $actualResult
            );
        }
    }

    /**
     * Verify image html declaration renders correctly with custom admin url.
     *
     * @magentoConfigFixture default/admin/url/use_custom 1
     * @magentoConfigFixture admin_store web/unsecure/base_url http://example-admin.com/
     */
    public function testGetImageHtmlDeclarationCustomBackendUrl()
    {
        $helper = $this->generateHelper(false, 0);
        $actualResult = $helper->getImageHtmlDeclaration('wysiwyg/hello.png', true);

        $this->assertEquals('<img src="{{media url=&quot;wysiwyg/hello.png&quot;}}" alt="" />', $actualResult);
    }

    /**
     * Data provider for testGetImageHtmlDeclaration
     *
     * @return array
     */
    public static function providerGetImageHtmlDeclaration()
    {
        return [
            [true, 'wysiwyg/hello.png', true, '<img src="http://example.com/media/wysiwyg/hello.png" alt="" />'],
            [
                false,
                'wysiwyg/hello.png',
                false,
                function ($actualResult) {
                    $expectedResult = (
                        '/backend/cms/wysiwyg/directive/___directive/' .
                        'e3ttZWRpYSB1cmw9Ind5c2l3eWcvaGVsbG8ucG5nIn19/'
                    );

                    self::assertStringContainsString($expectedResult, parse_url($actualResult, PHP_URL_PATH));
                },
            ],
            [true, 'wysiwyg/hello.png', false, 'http://example.com/media/wysiwyg/hello.png'],
            [false, 'wysiwyg/hello.png', true, '<img src="{{media url=&quot;wysiwyg/hello.png&quot;}}" alt="" />'],
        ];
    }

    /**
     * Generate instance of Images Helper
     *
     * @param bool $isStaticUrlsAllowed - mock is created to override value of isUsingStaticUrlsAllowed method in class
     * @param int $storeId
     * @return Images
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function generateHelper($isStaticUrlsAllowed = false, int $storeId = 1)
    {
        $eventManagerMock = $this->createMock(ManagerInterface::class);

        $contextMock = $this->objectManager->create(Context::class, [
            'eventManager' => $eventManagerMock,
        ]);

        $helper = $this->objectManager->create(Images::class, [
            'context' => $contextMock,
        ]);

        $checkResult = new stdClass();
        $checkResult->isAllowed = false;

        $eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with('cms_wysiwyg_images_static_urls_allowed', ['result' => $checkResult, 'store_id' => $storeId])
            ->willReturnCallback(function ($_, $arr) use ($isStaticUrlsAllowed) {
                $arr['result']->isAllowed = $isStaticUrlsAllowed;
            });

        $helper->setStoreId($storeId);

        return $helper;
    }
}
