<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

/**
 * Class \Magento\Setup\Module\Di\Code\Scanner\CompositeScanner
 *
 * @since 2.0.0
 */
class CompositeScanner implements ScannerInterface
{
    /**
     * @var ScannerInterface[]
     * @since 2.0.0
     */
    protected $_children = [];

    /**
     * Add child scanner
     *
     * @param ScannerInterface $scanner
     * @param string $type
     * @return void
     * @since 2.0.0
     */
    public function addChild(ScannerInterface $scanner, $type)
    {
        $this->_children[$type] = $scanner;
    }

    /**
     * Scan files
     *
     * @param array $files
     * @return array
     * @since 2.0.0
     */
    public function collectEntities(array $files)
    {
        $output = [];
        foreach ($this->_children as $type => $scanner) {
            if (!isset($files[$type]) || !is_array($files[$type])) {
                continue;
            }
            $output[$type] = array_unique($scanner->collectEntities($files[$type]));
        }
        return $output;
    }
}
