<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Compiler\Log\Writer;

use Magento\Setup\Module\Di\Compiler\Log\Log;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class \Magento\Setup\Module\Di\Compiler\Log\Writer\Console
 *
 * @since 2.0.0
 */
class Console
{
    /**
     * Report messages by type
     *
     * @var array
     * @since 2.0.0
     */
    protected $_messages = [
        Log::GENERATION_SUCCESS => 'Generated classes:',
        Log::GENERATION_ERROR => 'Errors during class generation:',
        Log::COMPILATION_ERROR => 'Errors during compilation:',
        Log::CONFIGURATION_ERROR => 'Errors during configuration scanning:',
    ];

    /**
     * Console
     *
     * @var OutputInterface
     * @since 2.0.0
     */
    protected $console;

    /**
     * @param OutputInterface $output
     * @since 2.0.0
     */
    public function __construct(OutputInterface $output)
    {
        $this->console = $output;
    }

    /**
     * Output log data
     *
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function write(array $data)
    {
        $errorsCount = 0;
        foreach ($data as $type => $classes) {
            if (!count($classes)) {
                continue;
            }
            $this->console->writeln($this->getStartTag($type) . $this->_messages[$type] . $this->getEndTag($type));
            foreach ($classes as $className => $messages) {
                if (count($messages)) {
                    $this->console->writeln($this->getStartTag($type) . "\t" . $className . $this->getEndTag($type));
                    foreach ($messages as $message) {
                        if ($message) {
                            $this->console->writeln(
                                $this->getStartTag($type) . "\t\t" . $message . $this->getEndTag($type)
                            );
                            if ($type != Log::GENERATION_SUCCESS) {
                                $errorsCount++;
                            }
                        }
                    }
                }
            }
        }

        if ($errorsCount) {
            $this->console->writeln('<error>' . 'Total Errors Count: ' . $errorsCount . '</error>');
        }
    }

    /**
     * Retrieve starting output tag
     *
     * @param string $type
     * @return string
     * @since 2.0.0
     */
    private function getStartTag($type)
    {
        if ($type === Log::GENERATION_SUCCESS) {
            return '<info>';
        } else {
            return '<error>';
        }
    }

    /**
     * Retrieve ending output tag
     *
     * @param string $type
     * @return string
     * @since 2.0.0
     */
    private function getEndTag($type)
    {
        if ($type === Log::GENERATION_SUCCESS) {
            return '</info>';
        } else {
            return '</error>';
        }
    }
}
