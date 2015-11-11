<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\App\Language;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Driver\File;

class TranslationFiles extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvParser;

    protected function setUp()
    {
        $this->csvParser = new \Magento\Framework\File\Csv(new File());
    }

    /**
     * @return array
     */
    public function getLocalePlacePath()
    {
        $pathToSource = \Magento\Framework\App\Utility\Files::init()->getPathToSource();
        $places = [];
        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $modulePath) {
            $places[basename($modulePath)] = ['placePath' => $modulePath];
        }
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themePath) {
            $placeName = basename(dirname(dirname($themePath))) . '_' . basename($themePath);
            $places[$placeName] = ['placePath' => $themePath];
        }
        $places['lib_web'] = ['placePath' => "{$pathToSource}/lib/web"];
        return $places;
    }

    /**
     * @param string $modulePath
     * @return string[] Array csv files array[$locale]$pathToCsvFile]
     */
    protected function getCsvFiles($modulePath)
    {
        $files = [];
        foreach (glob("{$modulePath}/i18n/*.csv") as $file) {
            $locale = str_replace('.csv', '', basename($file));
            $files[$locale] = $file;
        }
        return $files;
    }

    /**
     * @param array $baseLocaleData
     * @param array $localeData
     * @return array
     */
    protected function comparePhrase($baseLocaleData, $localeData)
    {
        $missing = array_diff_key($baseLocaleData, $localeData);
        $extra = array_diff_key($localeData, $baseLocaleData);

        $failures = [];
        if (!empty($missing)) {
            $failures['missing'] = array_keys($missing);
        }
        if (!empty($extra)) {
            $failures['extra'] =  array_keys($extra);
        }
        return $failures;
    }

    /**
     * @param string[][][] $failures Array errors in format $failures[$locale][$errorType][$message]
     * @param string $message
     * @return string
     */
    protected function printMessage($failures, $message = '')
    {
        $message .= PHP_EOL;
        foreach ($failures as $locale => $localeErrors) {
            $message .= $locale . PHP_EOL;
            foreach ($localeErrors as $typeError => $error) {
                $message .= PHP_EOL . "##########" . PHP_EOL . ucfirst($typeError) . ':' . PHP_EOL;
                foreach ($error as $phrase) {
                    $message .= '"' . $phrase . '","' . $phrase . '"' . PHP_EOL;
                }
            }
        }
        return $message;
    }
}
