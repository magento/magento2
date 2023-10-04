<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Model\Plugin\AsyncConfig\Model;

use Magento\AsyncConfig\Model\AsyncConfigPublisher;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;

class AsyncConfigPublisherPlugin
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Random
     */
    private Random $rand;

    /**
     * @var RequestFactory
     */
    private RequestFactory $requestFactory;

    /**
     * @param Filesystem $filesystem
     * @param Random $rand
     * @param RequestFactory $requestFactory
     */
    public function __construct(Filesystem $filesystem, Random $rand, RequestFactory $requestFactory)
    {
        $this->filesystem = $filesystem;
        $this->rand = $rand;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Save table rate import file for async processing
     *
     * @param AsyncConfigPublisher $subject
     * @param array $configData
     * @return array
     * @throws FileSystemException|LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveConfigData(AsyncConfigPublisher $subject, array $configData): array
    {
        $request = $this->requestFactory->create();
        $files = (array)$request->getFiles();

        if (!empty($files['groups']['tablerate']['fields']['import']['value']['name'])) {
            $varDir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
            $randomizedName = $this->rand->getRandomString(6) . '_' .
                $configData['groups']['tablerate']['fields']['import']['value']['name'];
            if (!$varDir->getDriver()
                ->copy(
                    $files['groups']['tablerate']['fields']['import']['value']['tmp_name'],
                    $varDir->getAbsolutePath($randomizedName)
                )) {
                throw new FileSystemException(__('Filesystem is not writable.'));
            }

            $configData['groups']['tablerate']['fields']['import']['value']['name'] = $randomizedName;
            $configData['groups']['tablerate']['fields']['import']['value']['full_path'] = $varDir->getAbsolutePath();
        }

        return [$configData];
    }
}
