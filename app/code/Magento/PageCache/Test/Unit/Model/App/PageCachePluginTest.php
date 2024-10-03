<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App;

use Magento\Framework\App\PageCache\Cache;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\App\PageCachePlugin;
use Magento\PageCache\Model\Cache\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageCachePluginTest extends TestCase
{
    /** @var PageCachePlugin */
    private $plugin;

    /** @var MockObject|Cache*/
    private $subjectMock;

    protected function setUp(): void
    {
        $this->plugin = (new ObjectManager($this))->getObject(PageCachePlugin::class);
        $this->subjectMock = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testBeforeSaveAddTag()
    {
        $initTags = ['tag', 'otherTag'];
        $result = $this->plugin->beforeSave($this->subjectMock, 'data', 'identifier', $initTags);
        $tags = isset($result[2]) ? $result[2] : null;
        $expectedTags = array_merge($initTags, [Type::CACHE_TAG]);
        $this->assertNotNull($tags);
        foreach ($expectedTags as $expected) {
            $this->assertContains($expected, $tags);
        }
    }

    public function testBeforeSaveCompression()
    {
        $data = 'raw-data';
        $expected = PageCachePlugin::COMPRESSION_PREFIX . gzcompress($data);
        $result = $this->plugin->beforeSave($this->subjectMock, $data, 'id');
        $resultData = $result[0];
        $this->assertSame($resultData, $expected);
    }

    /**
     * @dataProvider afterSaveDataProvider
     * @param string $dataw
     * @param string $initResult
     */
    public function testAfterSaveDecompression($data, $initResult)
    {
        $this->assertSame($data, $this->plugin->afterLoad($this->subjectMock, $initResult));
    }

    /**
     * @return array
     */
    public static function afterSaveDataProvider()
    {
        return [
            'Compressed cache' => ['raw-data', PageCachePlugin::COMPRESSION_PREFIX . gzcompress('raw-data')],
            'Non-compressed cache' => ['raw-data', 'raw-data']
        ];
    }
}
