<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Tools\Di\Compiler\Config;

class Plugins implements OperationInterface
{
    /**
     * @var Config\WriterInterface
     */
    private $configWriter;

    /**
     * @var array
     */
    private $data = '';

    /**
     * Code
     */
    const CODE = 'plugins';

    /**
     * @param Config\WriterInterface $configWriter
     * @param string $data
     */
    public function __construct(
        Config\WriterInterface $configWriter,
        $data = ''
    ) {
        $this->configWriter = $configWriter;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        $filePatterns = ['di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/'];

        $directoryScanner = new \Magento\Tools\Di\Code\Scanner\DirectoryScanner();
        $files = $directoryScanner->scan($this->data, $filePatterns);

        $pluginScanner = new \Magento\Tools\Di\Code\Scanner\CompositeScanner();
        $pluginScanner->addChild(new \Magento\Tools\Di\Code\Scanner\PluginScanner(), 'di');
        $pluginDefinitions = [];
        $pluginList = $pluginScanner->collectEntities($files);
        $pluginDefinitionList = new \Magento\Framework\Interception\Definition\Runtime();
        foreach ($pluginList as $type => $entityList) {
            foreach ($entityList as $entity) {
                $pluginDefinitions[$entity] = $pluginDefinitionList->getMethodList($entity);
            }
        }

        $this->configWriter->write(self::CODE, $pluginDefinitions);
    }
}
