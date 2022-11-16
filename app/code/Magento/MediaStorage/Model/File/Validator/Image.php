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
        $isValid = false;

        if (stripos(json_encode($this->imageMimeTypes), $fileMimeType) !== false) {
            $defaultAdapter = $this->config->getAdapterAlias();
            try {
                $image = $this->imageFactory->create($filePath, $defaultAdapter);
                $image->open();
                $isValid = true;
            } catch (\InvalidArgumentException $e) {
                $adapters = $this->config->getAdapters();
                unset($adapters[$defaultAdapter]);
                $image = $this->imageFactory->create($filePath, array_key_first($adapters) ?? null);
                $image->open();
                $isValid = true;
            } catch (\Exception $e) {
                $isValid = false;
                $this->logger->critical($e, ['exception' => $e]);
            }
        }

        return $isValid;
    }
}
