<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Proxy;

use Magento\Framework\DataObject;
use PHPUnit\Framework\Exception;

/**
 * File based data fixture
 */
class LegacyDataFixture implements DataFixtureInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @inheritdoc
     */
    public function apply(DataObject $data): ?DataObject
    {
        $this->execute($this->filePath);
        return null;
    }

    /**
     * @inheritdoc
     */
    public function revert(?DataObject $data): void
    {
        $fileInfo = pathinfo($this->filePath);
        $extension = '';
        if (isset($fileInfo['extension'])) {
            $extension = '.' . $fileInfo['extension'];
        }
        $rollbackScript = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '_rollback' . $extension;
        if (file_exists($rollbackScript)) {
            $this->execute($rollbackScript);
        }
    }

    /**
     * Execute file
     *
     * @param string $filePath
     */
    private function execute(string $filePath): void
    {
        try {
            require $filePath;
        } catch (\Exception $e) {
            throw new Exception(
                sprintf(
                    "Error in fixture: %s.\n %s\n %s",
                    $filePath,
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                500,
                $e
            );
        }
    }
}
