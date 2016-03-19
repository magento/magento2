<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test index action
     */
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/crypt_key/index');

        $body = $this->getResponse()->getBody();
        $this->assertContains('<h1 class="page-title">Encryption Key</h1>', $body);
    }
}
