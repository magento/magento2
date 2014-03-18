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
namespace Magento\Tools\Di\Compiler\Log\Writer;

use Magento\Tools\Di\Compiler\Log\Log;

class Console implements WriterInterface
{
    /**
     * Report messages by type
     *
     * @var array
     */
    protected $_messages = array(
        Log::GENERATION_SUCCESS => 'Generated classes:',
        Log::GENERATION_ERROR => 'Errors during class generation:',
        Log::COMPILATION_ERROR => 'Errors during compilation:',
        Log::CONFIGURATION_ERROR => 'Errors during configuration scanning:'
    );

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
