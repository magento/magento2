<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use PHPUnit\Framework\Attributes\DataProvider;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(Storage::class);
        parent::tearDown();
    }

    public function testViewAction()
    {
        $this->dispatch('backend/cms/wysiwyg_images/index/target_element_id/page_content/store/undefined/type/image/');
        $content = $this->getResponse()->getBody();
        $this->assertStringNotContainsString('<html', $content);
        $this->assertStringNotContainsString('<head', $content);
        $this->assertStringNotContainsString('<body', $content);
    }

    /**
     * @param string $type
     * @param string[] $expectedExtensions
     * @return void
     */
    #[DataProvider('mediaTypeDataProvider')]
    public function testUploaderReceivesStorageConfiguredExtensions(string $type, array $expectedExtensions): void
    {
        $storage = $this->_objectManager->create(
            Storage::class,
            [
                'extensions' => [
                    'allowed' => ['jpg' => 'image/jpg', 'pdf' => 'application/pdf'],
                    'image_allowed' => ['png' => 'image/png', 'webp' => 'image/webp'],
                ],
            ]
        );
        $this->_objectManager->addSharedInstance($storage, Storage::class);

        $this->dispatch(
            'backend/cms/wysiwyg_images/index/target_element_id/page_content/store/undefined/type/' . $type . '/'
        );

        $this->assertSame($expectedExtensions, $this->getUploaderAllowedExtensions($this->getResponse()->getBody()));
    }

    /**
     * @return array
     */
    public static function mediaTypeDataProvider(): array
    {
        return [
            'file' => ['file', ['jpg', 'pdf']],
            'image' => ['image', ['png', 'webp']],
        ];
    }

    /**
     * @param string $html
     * @return array|null
     */
    private function getUploaderAllowedExtensions(string $html): ?array
    {
        $dom = new \DOMDocument();
        $useInternalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);
        $xpath = new \DOMXPath($dom);
        $node = $xpath->query('//div[contains(concat(" ", @class, " "), " uploader ")][@data-mage-init]')->item(0);
        $this->assertNotNull($node, 'Uploader element with data-mage-init was not rendered.');
        $config = json_decode($node->getAttribute('data-mage-init'), true);
        $this->assertIsArray($config);

        return $config['Magento_Backend/js/media-uploader']['allowedExtensions'] ?? null;
    }
}
