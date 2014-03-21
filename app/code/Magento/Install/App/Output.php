<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\App;

class Output
{
    /**
     * Make array keys aligned to the longest
     *
     * @param array $data
     * @return array
     */
    public function alignArrayKeys(array $data)
    {
        $formattedData = array();
        $length = max(array_map('strlen', array_keys($data)));
        foreach ($data as $key => $value) {
            $formattedData[str_pad($key, $length, ' ', STR_PAD_RIGHT)] = $value;
        }
        return $formattedData;
    }

    /**
     * Process an array to $key => $value format
     * and adapt keys to pretty output
     *
     * @param array $rawData
     * @return array
     */
    public function prepareArray(array $rawData)
    {
        $keyValData = array();

        // transform data to key => value format
        foreach ($rawData as $item) {
            $keyValData[$item['value']] = $item['label'];
        }

        return $this->alignArrayKeys($keyValData);
    }

    /**
     * Make output human readable
     *
     * @param mixed $var
     * @return void
     */
    public function readableOutput($var)
    {
        switch (true) {
            case is_array($var):
                $eol = '';
                foreach ($var as $key => $value) {
                    if (is_array($value) || !is_scalar($value)) {
                        echo $eol . $key . ' => ' . var_export($value, true);
                    } else {
                        echo $eol . $key . ' -- ' . $value;
                    }
                    $eol = PHP_EOL;
                }
                echo PHP_EOL;
                break;
            case is_scalar($var):
                echo $var . PHP_EOL;
                break;
            default:
                var_export($var);
        }
    }

    /**
     * Display message
     *
     * @param string $message
     * @return void
     */
    public function success($message)
    {
        echo $message;
    }

    /**
     * Display error
     *
     * @param string $message
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function error($message)
    {
        echo $message;
        exit(1);
    }
}
