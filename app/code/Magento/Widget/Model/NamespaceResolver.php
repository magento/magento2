<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    protected $_moduleNamespaces;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(\Magento\Framework\Module\ModuleListInterface $moduleList)
    {
        $this->_moduleList = $moduleList;
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
        if (null === $this->_moduleNamespaces) {
            $this->_moduleNamespaces = [];
            foreach ($this->_moduleList->getNames() as $moduleName) {
                $module = strtolower($moduleName);
                $this->_moduleNamespaces[substr($module, 0, strpos($module, '_'))][$module] = $moduleName;
            }
        }

        $explodeString = strpos(
            $name,
            '\\'
        ) === false ? '_' : '\\';
        $name = explode($explodeString, strtolower($name));

        $partsNum = count($name);
        $defaultNamespaceFlag = false;
        foreach ($this->_moduleNamespaces as $namespaceName => $namespace) {
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
}
