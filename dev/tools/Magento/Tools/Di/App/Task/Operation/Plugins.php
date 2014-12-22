<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Tools\Di\Compiler\Config;
use Magento\Framework\Interception;

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
     * @var Interception\Definition\Runtime
     */
    private $definitionRuntime;

    /**
     * Code
     */
    const CODE = 'plugins';

    /**
     * @param Config\WriterInterface $configWriter
     * @param Interception\Definition\Runtime $definitionRuntime
     * @param string $data
     */
    public function __construct(
        Config\WriterInterface $configWriter,
        Interception\Definition\Runtime $definitionRuntime,
        $data = ''
    ) {
        $this->configWriter = $configWriter;
        $this->definitionRuntime = $definitionRuntime;
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
        foreach ($pluginList as $type => $entityList) {
            foreach ($entityList as $entity) {
                $pluginDefinitions[$entity] = $this->definitionRuntime->getMethodList($entity);
            }
        }

        $this->configWriter->write(self::CODE, $pluginDefinitions);
    }
}
