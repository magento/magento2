<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Cms\Controller\Page.
 */
namespace Magento\Cms\Controller\Noroute;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Cms/_files/noroute.php
     */
    public function testDisabledNoRoutePage()
    {
        $this->dispatch('/test123');
        $this->assertContains('There was no 404 CMS page configured or found.', $this->getResponse()->getBody());
    }
}
