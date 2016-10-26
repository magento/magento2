<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Section;

class LoadTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testLoadInvalidSection()
    {
        $expected = [
            'message' => '&quot;section&lt;invalid&quot; section source is not supported',
        ];
        $this->dispatch('/customer/section/load/?sections=section<invalid&update_section_id=false&_=147066166394');
        self::assertEquals(json_encode($expected), $this->getResponse()->getBody());
    }
}
