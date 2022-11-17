<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Model\File\Validator;

use Laminas\Validator\AbstractValidator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Image\Adapter\ConfigInterface;
use Magento\Framework\Image\Factory;
use Psr\Log\LoggerInterface;

/**
 * Image validator
 */
class Image extends AbstractValidator
{
    /**
     * @var array
     */
    private $imageMimeTypes = [
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => [ 'image/vnd.microsoft.icon', 'image/x-icon']
    ];

    /**
     * @var Mime
     */
    private $fileMime;

    /**
     * @var Factory
     */
    private $imageFactory;

    /**
     * @var File
     */
    private $file;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Mime $fileMime
     * @param Factory $imageFactory
     * @param File $file
     * @param ConfigInterface|null $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Mime $fileMime,
        Factory $imageFactory,
        File $file,
        ConfigInterface $config = null,
        LoggerInterface $logger = null
    ) {
        $this->fileMime = $fileMime;
        $this->imageFactory = $imageFactory;
        $this->file = $file;
        $this->config = $config ?? ObjectManager::getInstance()->get(ConfigInterface::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function isValid($filePath): bool
    {
        $fileMimeType = $this->fileMime->getMimeType($filePath);
        $isValid = true;

        if (stripos(json_encode($this->imageMimeTypes), json_encode($fileMimeType)) !== false) {
            try {
                $image = $this->imageFactory->create($filePath);
                $image->open();
            } catch (\InvalidArgumentException $e) {
                if (stripos($fileMimeType, 'icon') !== false) {
                    $image = $this->imageFactory->create($filePath, $this->getNonDefaultAdapter());
                    $image->open();
                }
            } catch (\Exception $e) {
                $isValid = false;
                $this->logger->critical($e, ['exception' => $e]);
            }
        }

        return $isValid;
    }

    /**
     * Get non default image adapter
     *
     * @return string|null
     */
    private function getNonDefaultAdapter(): ?string
    {
        $defaultAdapter = $this->config->getAdapterAlias();
        $adapters = $this->config->getAdapters();
        unset($adapters[$defaultAdapter]);
        return array_key_first($adapters) ?? null;
    }
}
