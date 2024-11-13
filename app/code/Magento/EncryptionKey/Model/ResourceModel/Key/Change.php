<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\EncryptionKey\Model\ResourceModel\Key;

use \Exception;
use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Encryption key changer resource model
 * The operation must be done in one transaction
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Change extends AbstractDb
{
    /**
     * Encryptor interface
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Filesystem directory write interface
     *
     * @var WriteInterface
     */
    protected $directory;

    /**
     * System configuration structure
     *
     * @var Structure
     */
    protected $structure;

    /**
     * Configuration writer
     *
     * @var Writer
     */
    protected $writer;

    /**
     * Random string generator
     *
     * @var Random
     * @since 100.0.4
     */
    protected $random;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param Structure $structure
     * @param EncryptorInterface $encryptor
     * @param Writer $writer
     * @param Random $random
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        Structure $structure,
        EncryptorInterface $encryptor,
        Writer $writer,
        Random $random,
        $connectionName = null
    ) {
        $this->encryptor = clone $encryptor;
        parent::__construct($context, $connectionName);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        $this->structure = $structure;
        $this->writer = $writer;
        $this->random = $random;
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_config_data', 'config_id');
    }

    /**
     * Change encryption key
     *
     * @param string|null $key
     * @return null|string
     * @throws FileSystemException|LocalizedException|Exception
     */
    public function changeEncryptionKey($key = null)
    {
        // prepare new key, encryptor and new configuration segment
        if (!$this->writer->checkIfWritable()) {
            throw new FileSystemException(__('Deployment configuration file is not writable.'));
        }

        if (null === $key) {
            $key = ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX .
                $this->random->getRandomBytes(ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE);
        }
        $this->encryptor->setNewKey($key);

        $encryptSegment = new ConfigData(ConfigFilePool::APP_ENV);
        $encryptSegment->set(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY, $this->encryptor->exportKeys());

        $configData = [$encryptSegment->getFileKey() => $encryptSegment->getData()];

        // update database and config.php
        $this->beginTransaction();
        try {
            $this->_reEncryptSystemConfigurationValues();
            $this->_reEncryptCreditCardNumbers();
            $this->writer->saveConfig($configData);
            $this->commit();
            return $key;
        } catch (LocalizedException $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Gather all encrypted system config values and re-encrypt them
     *
     * @return void
     */
    protected function _reEncryptSystemConfigurationValues()
    {
        // look for encrypted node entries in all system.xml files
        /** @var Structure $configStructure  */
        $configStructure = $this->structure;
        $paths = $configStructure->getFieldPathsByAttribute(
            'backend_model',
            Encrypted::class
        );

        // walk through found data and re-encrypt it
        if ($paths) {
            $table = $this->getTable('core_config_data');
            $values = $this->getConnection()->fetchPairs(
                $this->getConnection()
                    ->select()
                    ->from($table, ['config_id', 'value'])
                    ->where('path IN (?)', $paths)
                    ->where('value NOT LIKE ?', '')
            );
            foreach ($values as $configId => $value) {
                $this->getConnection()->update(
                    $table,
                    ['value' => $this->encryptor->encrypt($this->encryptor->decrypt($value))],
                    ['config_id = ?' => (int)$configId]
                );
            }
        }
    }

    /**
     * Gather saved credit card numbers from sales order payments and re-encrypt them
     *
     * @return void
     */
    protected function _reEncryptCreditCardNumbers()
    {
        $table = $this->getTable('sales_order_payment');
        $select = $this->getConnection()->select()->from($table, ['entity_id', 'cc_number_enc']);

        $attributeValues = $this->getConnection()->fetchPairs($select);
        // save new values
        foreach ($attributeValues as $valueId => $value) {
            $this->getConnection()->update(
                $table,
                ['cc_number_enc' => $this->encryptor->encrypt($this->encryptor->decrypt($value))],
                ['entity_id = ?' => (int)$valueId]
            );
        }
    }
}
