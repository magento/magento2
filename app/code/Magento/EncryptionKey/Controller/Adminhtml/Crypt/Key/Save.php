<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key;

/**
 * Encryption Key Save Controller
 * @since 2.0.0
 */
class Save extends \Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    protected $encryptor;

    /**
     * @var \Magento\EncryptionKey\Model\ResourceModel\Key\Change
     * @since 2.0.0
     */
    protected $change;

    /**
     * @var \Magento\Framework\App\CacheInterface
     * @since 2.0.0
     */
    protected $cache;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\EncryptionKey\Model\ResourceModel\Key\Change $change
     * @param \Magento\Framework\App\CacheInterface $cache
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\EncryptionKey\Model\ResourceModel\Key\Change $change,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->encryptor = $encryptor;
        $this->change = $change;
        $this->cache = $cache;
        parent::__construct($context);
    }

    /**
     * Process saving new encryption key
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $key = null;

            if (0 == $this->getRequest()->getPost('generate_random')) {
                $key = $this->getRequest()->getPost('crypt_key');
                if (empty($key)) {
                    throw new \Exception(__('Please enter an encryption key.'));
                }
                $this->encryptor->validateKey($key);
            }

            $newKey = $this->change->changeEncryptionKey($key);
            $this->messageManager->addSuccessMessage(__('The encryption key has been changed.'));

            if (!$key) {
                $this->messageManager->addNoticeMessage(
                    __(
                        'This is your new encryption key: %1. ' .
                        'Be sure to write it down and take good care of it!',
                        $newKey
                    )
                );
            }
            $this->cache->clean();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_session->setFormData(['crypt_key' => $key]);
        }
        $this->_redirect('adminhtml/*/');
    }
}
