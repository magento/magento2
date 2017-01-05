<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model;

class NamespaceResolver
{
    /**
     * List of module namespaces
     *
     * @var array
     */
    protected $moduleNamespaces;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(\Magento\Framework\Module\ModuleListInterface $moduleList)
    {
        $this->moduleList = $moduleList;
    }

    /**
     * Determine whether provided name begins from any available modules, according to namespaces priority
     * If matched, returns as the matched module "factory" name or a fully qualified module name
     *
     * @param string $name
     * @param bool $asFullModuleName
     * @return string
     */
    public function determineOmittedNamespace($name, $asFullModuleName = false)
    {
        $this->prepareModuleNamespaces();
        $name = $this->prepareName($name);

        $partsNum = count($name);
        $defaultNamespaceFlag = false;
        foreach ($this->moduleNamespaces as $namespaceName => $namespace) {
            // assume the namespace is omitted (default namespace only, which comes first)
            if ($defaultNamespaceFlag === false) {
                $defaultNamespaceFlag = true;
                $defaultNS = $namespaceName . '_' . $name[0];
                if (isset($namespace[$defaultNS])) {
                    return $asFullModuleName ? $namespace[$defaultNS] : $name[0]; // return omitted as well
                }
            }
            // assume namespace is qualified
            if (isset($name[1])) {
                $fullNS = $name[0] . '_' . $name[1];
                if (2 <= $partsNum && isset($namespace[$fullNS])) {
                    return $asFullModuleName ? $namespace[$fullNS] : $fullNS;
                }
            }
        }
        return '';
    }

    /**
     * Prepare module namespaces
     *
     * @return void
     */
    protected function prepareModuleNamespaces()
    {
        if (null === $this->moduleNamespaces) {
            $this->moduleNamespaces = [];
            foreach ($this->moduleList->getNames() as $moduleName) {
                $module = strtolower($moduleName);
                $this->moduleNamespaces[substr($module, 0, strpos($module, '_'))][$module] = $moduleName;
            }
        }
    }

    /**
     * Prepare name
     *
     * @param string $name
     * @return array
     */
    protected function prepareName($name)
    {
        $explodeString = strpos($name, '\\') === false ? '_' : '\\';
        return explode($explodeString, strtolower($name));
    }
}
