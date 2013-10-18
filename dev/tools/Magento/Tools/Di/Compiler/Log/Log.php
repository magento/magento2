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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Di\Compiler\Log;
use Magento\Tools\Di\Compiler\Log\Writer;

class Log
{
    const GENERATION_ERROR = 1;
    const GENERATION_SUCCESS = 2;
    const COMPILATION_ERROR = 3;

    /**
     * Log writer
     *
     * @var Writer\WriterInterface
     */
    protected $_writer;

    /**
     * List of log entries
     *
     * @var array
     */
    protected $_entries = array();

    /**
     * Allowed log types
     *
     * @var array
     */
    protected $_allowedTypes;

    /**
     * @param Writer\WriterInterface $writer
     * @param array $allowedTypes
     */
    public function __construct(Writer\WriterInterface $writer, $allowedTypes = array())
    {
        $this->_writer = $writer;
        $this->_allowedTypes = empty($allowedTypes)
            ? array(self::GENERATION_ERROR, self::COMPILATION_ERROR, self::GENERATION_SUCCESS)
            : $allowedTypes;
    }

    /**
     * Add log message
     *
     * @param string $type
     * @param string $key
     * @param string $message
     */
    public function add($type, $key, $message = '')
    {
        if (in_array($type, $this->_allowedTypes)) {
            $this->_entries[$type][$key][] = $message;
        }
    }

    /**
     * Write entries
     */
    public function report()
    {
        $this->_writer->write($this->_entries);
    }
}
