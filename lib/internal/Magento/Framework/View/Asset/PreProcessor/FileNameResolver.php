<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * The filename resolver
 */
class FileNameResolver
{
    /**
     * @var AlternativeSource[]
     */
    private $alternativeSources;

    /**
     * FileNameResolver constructor.
     * @param array $alternativeSources
     * @internal param AlternativeSource $alternativeSource
     */
    public function __construct(array $alternativeSources = [])
    {
        $this->alternativeSources = array_map(
            function (AlternativeSourceInterface $alternativeSource) {
                return $alternativeSource;
            },
            $alternativeSources
        );
    }

    /**
     * Resolve filename
     *
     * @param string $fileName
     * @return string
     */
    public function resolve(string $fileName): string
    {
        $compiledFile = $fileName;
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        foreach ($this->alternativeSources as $name => $alternative) {
            if ($alternative->isExtensionSupported($extension) && !str_starts_with(basename($fileName), '_')) {
                $compiledFile = substr($fileName, 0, strlen($fileName) - strlen($extension) - 1);
                $compiledFile = sprintf('%s.%s', $compiledFile, $name);
            }
        }

        return $compiledFile;
    }
}
