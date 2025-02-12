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
    private string $filePath;

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
        require $this->filePath;
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
            require $rollbackScript;
        }
    }
}
