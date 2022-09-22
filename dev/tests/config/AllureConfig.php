<?php

class AllureConfig
{
    public static function getAllureConfig($outputDirectory)
    {
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        return [
            // Path to output directory (default is build/allure-results)
            'outputDirectory' => $outputDirectory,
            'setupHook' => function () use ($outputDirectory): void {
                $files = scandir($outputDirectory);
                foreach ($files as $file) {
                    $filePath = $outputDirectory . DIRECTORY_SEPARATOR . $file;
                    if (is_file($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        ];
    }
}
