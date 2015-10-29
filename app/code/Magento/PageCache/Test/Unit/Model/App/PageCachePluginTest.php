<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Model\App;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Cache\Type;

class PageCachePluginTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeSave()
    {
        /** @var \Magento\PageCache\Model\App\PageCachePlugin $plugin */
        $plugin = (new ObjectManager($this))->getObject('\Magento\PageCache\Model\App\PageCachePlugin');
        $subjectMock = $this->getMockBuilder('\Magento\Framework\App\PageCache\Cache')
            ->disableOriginalConstructor()
            ->getMock();
        $initTags = ['tag', 'otherTag'];
        $result = $plugin->beforeSave($subjectMock, 'data', 'identifier', $initTags);
        $tags = isset($result[2]) ? $result[2] : null;
        $expectedTags = array_merge($initTags, [Type::CACHE_TAG]);
        $this->assertNotNull($tags);
        foreach ($expectedTags as $expected) {
            $this->assertContains($expected, $tags);
        }
    }
}
