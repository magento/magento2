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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Migration\Acl\Db;

class Updater
{
    const WRITE_MODE = 'write';

    /**
     * Resource id reader
     *
     * @var \Magento\Tools\Migration\Acl\Db\Reader
     */
    protected $_reader;

    /**
     * Resource id writer
     *
     * @var \Magento\Tools\Migration\Acl\Db\Writer
     */
    protected $_writer;

    /**
     * Operation logger
     *
     * @var \Magento\Tools\Migration\Acl\Db\AbstractLogger
     */
    protected $_logger;

    /**
     * Migration mode
     *
     * @var string
     */
    protected $_mode;

    /**
     * @param \Magento\Tools\Migration\Acl\Db\Reader $reader
     * @param \Magento\Tools\Migration\Acl\Db\Writer $writer
     * @param \Magento\Tools\Migration\Acl\Db\AbstractLogger $logger
     * @param string $mode - if value is "preview" migration does not happen
     */
    public function __construct(
        \Magento\Tools\Migration\Acl\Db\Reader $reader,
        \Magento\Tools\Migration\Acl\Db\Writer $writer,
        \Magento\Tools\Migration\Acl\Db\AbstractLogger $logger,
        $mode
    ) {
        $this->_reader = $reader;
        $this->_writer = $writer;
        $this->_logger = $logger;
        $this->_mode = $mode;
    }

    /**
     * Migrate old keys to new
     *
     * @param array $map
     * @return void
     */
    public function migrate($map)
    {
        foreach ($this->_reader->fetchAll() as $oldKey => $count) {
            $newKey = isset($map[$oldKey]) ? $map[$oldKey] : null;
            if (in_array($oldKey, $map)) {
                $newKey = $oldKey;
                $oldKey = null;
            }
            if ($newKey && $oldKey && $this->_mode == self::WRITE_MODE) {
                $this->_writer->update($oldKey, $newKey);
            }
            $this->_logger->add($oldKey, $newKey, $count);
        }
    }
}
