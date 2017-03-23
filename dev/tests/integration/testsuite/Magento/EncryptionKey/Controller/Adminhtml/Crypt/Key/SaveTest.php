<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key;

class SaveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test save action with empty encryption key
     */
    public function testSaveActionWithEmptyKey()
    {
        // data set with no random encryption key and no provided encryption key
        $ifGenerateRandom = '0';
        $encryptionKey = '';

        $request = $this->getRequest();
        $request
            ->setPostValue('generate_random', $ifGenerateRandom)
            ->setPostValue('crypt_key', $encryptionKey);
        $this->dispatch('backend/admin/crypt_key/save');
        $this->assertSessionMessages(
            $this->contains('Please enter an encryption key.'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test save action with provided encryption key
     *
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithProvidedKey()
    {
        $this->markTestSkipped('Test is blocked by MAGETWO-33612.');

        // data set with provided encryption key
        $ifGenerateRandom = '0';
        $encryptionKey = 'foo_encryption_key';

        $request = $this->getRequest();
        $request
            ->setPostValue('generate_random', $ifGenerateRandom)
            ->setPostValue('crypt_key', $encryptionKey);
        $this->dispatch('backend/admin/crypt_key/save');

        $this->assertRedirect();
        $this->assertSessionMessages(
            $this->contains('The encryption key has been changed.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Test save action with invalid encryption key
     */
    public function testSaveActionWithInvalidKey()
    {
        // data set with provided encryption key
        $ifGenerateRandom = '0';
        $encryptionKey = 'invalid key';

        $params = [
            'generate_random' => $ifGenerateRandom,
            'crypt_key' => $encryptionKey,
        ];
        $this->getRequest()->setPostValue($params);
        $this->dispatch('backend/admin/crypt_key/save');

        $this->assertRedirect();
        $this->assertSessionMessages(
            $this->contains('The encryption key format is invalid.'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test save action with randomly generated key
     *
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithRandomKey()
    {
        $this->markTestSkipped('Test is blocked by MAGETWO-33612.');

        // data set with random encryption key
        $ifGenerateRandom = '1';
        $encryptionKey = '';

        $request = $this->getRequest();
        $request
            ->setPostValue('generate_random', $ifGenerateRandom)
            ->setPostValue('crypt_key', $encryptionKey);
        $this->dispatch('backend/admin/crypt_key/save');

        $this->assertRedirect();
        $this->assertSessionMessages(
            $this->contains('The encryption key has been changed.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
