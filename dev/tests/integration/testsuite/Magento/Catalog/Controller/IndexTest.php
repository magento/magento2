<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Catalog\Controller\Index.
 */
namespace Magento\Catalog\Controller;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testIndexAction()
    {
        $this->dispatch('catalog/index');

        $this->assertRedirect();
    }
}
