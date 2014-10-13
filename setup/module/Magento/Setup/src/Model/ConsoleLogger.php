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

namespace Magento\Setup\Model;

use Zend\Console\Console;
use Zend\Console\ColorInterface;

/**
 * Console Logger
 *
 * @package Magento\Setup\Model
 */
class ConsoleLogger implements LoggerInterface
{

    /**
     * Console
     *
     * @var \Zend\Console\Adapter\AdapterInterface
     */
    protected $console;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->console = Console::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function logSuccess($message)
    {
        $this->console->writeLine("[SUCCESS]" . ($message ? ": $message" : ''), ColorInterface::LIGHT_GREEN);
    }

    /**
     * {@inheritdoc}
     */
    public function logError(\Exception $e)
    {
        $this->console->writeLine("[ERROR]: " . $e, ColorInterface::LIGHT_RED);
    }

    /**
     * {@inheritdoc}
     */
    public function log($message)
    {
        $this->console->writeLine($message, ColorInterface::LIGHT_BLUE);
    }

    /**
     * {@inheritdoc}
     */
    public function logMeta($message)
    {
        $this->console->writeLine($message, ColorInterface::GRAY);
    }
}
