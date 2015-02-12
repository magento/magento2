<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Template;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testMediaDirective()
    {
        $image = 'wysiwyg/VB.png';
        $construction = ['{{media url="' . $image . '"}}', 'media', ' url="' . $image . '"'];
        $baseUrl = 'http://localhost/pub/media/';

        /** @var \Magento\Widget\Model\Template\Filter $filter */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Template\Filter'
        );
        $result = $filter->mediaDirective($construction);
        $this->assertEquals($baseUrl . $image, $result);
    }
}
