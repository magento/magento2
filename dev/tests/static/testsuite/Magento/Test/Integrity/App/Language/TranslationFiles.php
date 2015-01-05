<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Test\Integrity\App\Language;

class TranslationFiles extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvParser;

    protected function setUp()
    {
        $this->csvParser = new \Magento\Framework\File\Csv();
        $this->csvParser->setDelimiter(',');
    }

    /**
     * @return array
     */
    public function getLocalePlacePath()
    {
        $pathToSource = \Magento\Framework\Test\Utility\Files::init()->getPathToSource();
        $places = [];
        foreach (glob("{$pathToSource}/vendor/magento/*/*", GLOB_ONLYDIR) as $modulePath) {
            $places[basename($modulePath)] = ['placePath' => $modulePath];
        }
        foreach (glob("{$pathToSource}/app/design/*/*/*", GLOB_ONLYDIR) as $themePath) {
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
