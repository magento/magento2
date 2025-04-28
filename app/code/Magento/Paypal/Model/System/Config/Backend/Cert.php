<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Backend;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Backend model for saving certificate file in case of using certificate based authentication
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Cert extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Paypal\Model\CertFactory
     */
    protected $_certFactory;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_tmpDirectory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Paypal\Model\CertFactory $certFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Paypal\Model\CertFactory $certFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Filesystem $filesystem,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_certFactory = $certFactory;
        $this->_encryptor = $encryptor;
        $this->_tmpDirectory = $filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process additional data before save config
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (!empty($value['value'])) {
            $this->setValue($value['value']);
        }

        if (is_array($value) && !empty($value['delete'])) {
            $this->setValue('');
            $this->_certFactory->create()->loadByWebsite($this->getScopeId())->delete();
        }

        if (empty($value['tmp_name'])) {
            return $this;
        }

        $tmpPath = $this->_tmpDirectory->getRelativePath($value['tmp_name']);

        if ($tmpPath && $this->_tmpDirectory->isExist($tmpPath)) {
            if (!$this->_tmpDirectory->stat($tmpPath)['size']) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The PayPal certificate file is empty.'));
            }
            $this->setValue($value['name']);
            $content = $this->_encryptor->encrypt($this->_tmpDirectory->readFile($tmpPath));
            $this->_certFactory->create()->loadByWebsite($this->getScopeId())->setContent($content)->save();
        }
        return $this;
    }

    /**
     * Process object after delete data
     *
     * @return $this
     */
    public function afterDelete()
    {
        $this->_certFactory->create()->loadByWebsite($this->getScopeId())->delete();
        return $this;
    }
}
