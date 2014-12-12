<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Di\Compiler\Log\Writer;

use Magento\Tools\Di\Compiler\Log\Log;

class Console implements WriterInterface
{
    /**
     * Report messages by type
     *
     * @var array
     */
    protected $_messages = [
        Log::GENERATION_SUCCESS => 'Generated classes:',
        Log::GENERATION_ERROR => 'Errors during class generation:',
        Log::COMPILATION_ERROR => 'Errors during compilation:',
        Log::CONFIGURATION_ERROR => 'Errors during configuration scanning:',
    ];

    /**
     * Output log data
     *
     * @param array $data
     * @return void
     */
    public function write(array $data)
    {
        $errorsCount = 0;
        foreach ($data as $type => $classes) {
            if (!count($classes)) {
                continue;
            }
            echo $this->_messages[$type] . "\n";
            foreach ($classes as $className => $messages) {
                if (count($messages)) {
                    echo "\t" . $className . "\n";
                    foreach ($messages as $message) {
                        if ($message) {
                            echo "\t\t - " . $message . "\n";
                            if ($type != Log::GENERATION_SUCCESS) {
                                $errorsCount++;
                            }
                        }
                    }
                }
            }
        }

        if ($errorsCount) {
            echo 'Total Errors Count: ' . $errorsCount . "\n";
        }
    }
}
