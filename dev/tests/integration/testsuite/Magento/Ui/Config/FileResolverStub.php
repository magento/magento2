<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Config;

use Magento\Framework\Config\FileIterator;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Module\Dir;

class FileResolverStub implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * @var array
     */
    private $files = [
        'etc/definition.xml' => [
            'module_one/ui_component/etc/test_definition.xml',
            'module_two/ui_component/etc/test_definition.xml'
        ],
        'etc/definition.map.xml' => [
            'ui_component/etc/definition.map.xml'
        ],
        'etc/test_definition.xml' => [
            'module_one/ui_component/etc/test_definition.xml',
            'module_two/ui_component/etc/test_definition.xml'
        ],
        'test_component.xml' => [
            'module_one/ui_component/test_component.xml',
            'module_two/ui_component/test_component.xml'
        ],
        'parent_component.xml' => [
            'module_one/ui_component/parent_component.xml',
            'module_two/ui_component/parent_component.xml'
        ]
    ];

    /**
     * @var Dir\Reader
     */
    private $moduleReader;

    /**
     * FileResolverStub constructor.
     *
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param array $files
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader, array $files = [])
    {
        $this->files = array_replace($this->files, $files);
        $this->moduleReader = $moduleReader;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        if ($filename == 'etc/definition.map.xml') {
            $path = $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Magento_Ui') . '/base';
        } else {
            $path = realpath(__DIR__ . '/../_files/view');
        }
        $files = isset($this->files[$filename]) ? $this->files[$filename] : [];
        $realPaths = [];
        foreach ($files as $filePath) {
            $realPaths[] = $path . '/' . $filePath;
        }
        return new FileIterator(new ReadFactory(new DriverPool), $realPaths);
    }
}
