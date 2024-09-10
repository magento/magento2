<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\EncryptionKey\Console\Command;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Math\Random;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class UpdateEncryptionKeyCommand extends Command
{
    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * Configuration writer
     *
     * @var Writer
     */
    private Writer $writer;

    /**
     * Random string generator
     *
     * @var Random
     */
    private Random $random;

    /**
     * @param EncryptorInterface $encryptor
     * @param CacheInterface $cache
     * @param Writer $writer
     * @param Random $random
     */
    public function __construct(EncryptorInterface $encryptor, CacheInterface $cache, Writer $writer, Random $random)
    {
        $this->encryptor = $encryptor;
        $this->cache = $cache;
        $this->writer = $writer;
        $this->random = $random;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('encryption:key:change');
        $this->setDescription('Change the encryption key inside the env.php file.');
        $this->addOption(
            'key',
            'k',
            InputOption::VALUE_OPTIONAL,
            'Key has to be a 32 characters long string. If not provided, a random key will be generated.'
        );

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $key = $input->getOption('key');

            if (!empty($key)) {
                $this->encryptor->validateKey($key);
            }

            $this->updateEncryptionKey($key);
            $this->cache->clean();

            $output->writeln('<info>Encryption key has been updated successfully.</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    /**
     * Update encryption key
     *
     * @param string|null $key
     * @return void
     * @throws FileSystemException
     */
    private function updateEncryptionKey(string $key = null): void
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

        $this->writer->saveConfig($configData);
    }
}
