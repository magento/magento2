<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Math\Random;

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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory
     * @param \Magento\Framework\Validator\File\IsImage $isImageValidator
     * @param Random|null $random
     * @param Request|null $request
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Framework\Validator\File\IsImage $isImageValidator,
        Random $random = null,
        Request $request = null
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
