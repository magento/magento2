<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\DataObject;

/**
 * File based data fixture
 */
class LegacyDataFixture implements RevertibleDataFixtureInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param LegacyDataFixturePathResolver $fixturePathResolver
     * @param string $filePath
     */
    public function __construct(
        LegacyDataFixturePathResolver $fixturePathResolver,
        string $filePath
    ) {
        $this->filePath = $fixturePathResolver->resolve($filePath);
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $this->execute($this->filePath);
        return null;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
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
        } catch (\Throwable $e) {
            throw new \Exception(
                'Error in fixture: ' . $filePath
                . PHP_EOL . $e->getMessage()
                . PHP_EOL . $e->getTraceAsString(),
                0,
                $e
            );
        }
    }
}
