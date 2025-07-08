<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Validator\File\IsImage;

/**
 * Request Aware Validator to replace use of $_SERVER super global.
 */
class RequestAwareValidatorFile extends ValidatorFile
{
    /**
     * @var Request $request
     */
    private Request $request;

    /**
     * Constructor method
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param Size $fileSize
     * @param FileTransferFactory $httpFactory
     * @param IsImage $isImageValidator
     * @param Random|null $random
     * @param Request|null $request
     * @throws FileSystemException
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Size $fileSize,
        FileTransferFactory $httpFactory,
        IsImage $isImageValidator,
        ?Random $random = null,
        ?Request $request = null
    ) {
        $this->request = $request ?: ObjectManager::getInstance()->get(Request::class);
        parent::__construct(
            $scopeConfig,
            $filesystem,
            $fileSize,
            $httpFactory,
            $isImageValidator,
            $random
        );
    }

    /**
     * @inheritDoc
     */
    protected function validateContentLength(): bool
    {
        return isset($this->request->getServer()['CONTENT_LENGTH'])
            && $this->request->getServer()['CONTENT_LENGTH'] > $this->fileSize->getMaxFileSize();
    }
}
