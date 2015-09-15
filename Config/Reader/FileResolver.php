<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MysqlMq\Config\Reader;

/**
 * Config file resolver for queue.xml files, which reads configs defined in tests.
 */
class FileResolver extends \Magento\Framework\App\Config\FileResolver
{
    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        return $this->merge(parent::get($filename, $scope), $this->getIntegrationTestConfigFiles());
    }

    /**
     * Merge arrays or \Magento\Framework\Config\FileIterator into an array
     *
     * @param mixed $array1
     * @param mixed $array2,...
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function merge($array1, $array2)
    {
        $arguments = func_get_args();
        $arraysToMerge = [];
        foreach ($arguments as $argument) {
            $arraysToMerge[] = is_array($argument) ? $argument : $this->convertToArray($argument);
        }
        return call_user_func_array('array_merge', $arraysToMerge);
    }

    /**
     * Return a list of test config files
     *
     * Looks for config files located at dev/tests/integration/testsuite/Magento/*\/etc/queue.xml
     *
     * @return array
     */
    protected function getIntegrationTestConfigFiles()
    {
        $filePatterns = [
            'dev/tests/integration/testsuite/Magento/*/etc/queue.xml'
        ];

        $filesArray = [];
        foreach ($filePatterns as $pattern) {
            foreach (glob(BP . '/' . $pattern) as $file) {
                $content = file_get_contents($file);
                if ($content) {
                    $filesArray[$file] = $content;
                }
            }
        }
        return $filesArray;
    }

    /**
     * Convert an argument to an array
     *
     * If it's not FileIterator instance, then empty array will be returned
     *
     * @param mixed $argument
     * @return array
     */
    protected function convertToArray($argument)
    {
        $resultArray = [];
        if ($argument instanceof \Magento\Framework\Config\FileIterator) {
            $resultArray = $argument->toArray();
        }
        return $resultArray;
    }
}
