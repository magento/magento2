<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

const KEY_COMPOSER_COMPONENT_TYPE = 'type';
const KEY_COMPOSER_MAGENTO2_MODULE = 'magento2-module';
const KEY_COMPOSER_REQUIRE = 'require';
const KEY_COMPOSER_COMPONENT_NAME = 'name';

const KEY_MODULE_DIRECTORY_NAME = 'module_directory_name';
const KEY_MODULE_DEPENDENT_MODULES = 'dependent_modules';
const KEY_MODULE_NAME = 'name';
const KEY_MODULE_COMPONENT = 'component';

const KEY_COMPONENT_MODULES = 'modules';
const KEY_COMPONENT_NAME = 'name';
const KEY_COMPONENT_DEPENDENCIES = 'dependencies';
const KEY_COMPONENT_DEPENDENTS = 'dependents';

const KEY_MAGENTO_CORE_MODULE = 'magento/module-core';

$modules = [];
$componentsByName = [];

define(
    'USAGE',
    "Usage: php -f dependency.php -- [--list-modules] [--list-components][--list-component-dependencies component_name]
        [--list-component-dependents component_name] [--list-module-dependencies module-name]
        [--list module-dependents module-name] [--direct-dependency-only]
        --help - print usage message
        --list-modules - list all modules in order of module dependency
        --list-components - list all components consisting of circularly dependent modules
        --list-component-dependencies - list components that the specified component depends on
        --list-component-dependents - list components that depends on the specified components
        --list-module-dependencies - list modules that the specified module depends on
        --list-module-dependents - list modules that depends on the specified module
        --direct-dependency-only - only return direct dependencies
        \n"
);
$opt = getopt(
    '',
    [
        'help',
        'list-modules',
        'list-components',
        'list-component-dependencies:',
        'list-component-dependents:',
        'list-module-dependencies:',
        'list-module-dependents:',
        'direct-dependency-only',
    ]
);

if (empty($opt) || isset($opt['help'])) {
    echo USAGE;
}

initialize();
$directDependenciesOnly = isset($opt['direct-dependency-only']) ? true : false;

if (isset($opt['list-modules'])) {
    $sortedComponents = topologicalSort($componentsByName);
    $result = [];
    foreach ($sortedComponents as $component) {
        foreach ($componentsByName[$component][KEY_COMPONENT_MODULES] as $module) {
            $result[] = $module;
        }
    }
    echo json_encode($result, JSON_PRETTY_PRINT);
} elseif (isset($opt['list-components'])) {
    $sortedComponents = topologicalSort($componentsByName);
    $result = [];
    foreach ($sortedComponents as $componentName) {
        $component = ['name' => $componentName];
        foreach ($componentsByName[$componentName][KEY_COMPONENT_MODULES] as $module) {
            $component['modules'][] = $module;
        }
        $result[] = $component;
    }
    echo json_encode($result, JSON_PRETTY_PRINT);
} elseif (isset($opt['list-component-dependencies'])) {
    //Get components that the specified component depends on, directly or indirectly
    $targetComponent = $opt['list-component-dependencies'];
    if (!isset($componentsByName[$targetComponent])) {
        die("Can't find specified component: " . $targetComponent . "\n");
    }
    if ($directDependenciesOnly) {
        $dependencies = $componentsByName[$targetComponent][KEY_COMPONENT_DEPENDENCIES];
    } else {
        $dependencies = getComponentDependency($targetComponent, KEY_COMPONENT_DEPENDENCIES);
    }
    echo json_encode($dependencies, JSON_PRETTY_PRINT);
} elseif (isset($opt['list-component-dependents'])) {
    //Get components that depends on the specified component, directly or indirectly
    $targetComponent = $opt['list-component-dependents'];
    if (!isset($componentsByName[$targetComponent])) {
        die("Can't find specified component: " . $targetComponent . "\n");
    }
    if ($directDependenciesOnly) {
        $dependencies = $componentsByName[$targetComponent][KEY_COMPONENT_DEPENDENTS];
    } else {
        $dependencies = getComponentDependency($targetComponent, KEY_COMPONENT_DEPENDENTS);
    }
    echo json_encode($dependencies, JSON_PRETTY_PRINT);
} elseif (isset($opt['list-module-dependents'])) {
    //Get modules that depends on the specified module, directly or indirectly
    $targetModule = $opt['list-module-dependents'];
    $dependencies = [];
    if (!isset($modules[$targetModule])) {
        die("Can't find specified module: " . $targetModule . "\n");
    }

    if ($directDependenciesOnly) {
        foreach ($modules as $module) {
            if (in_array($targetModule, $module[KEY_MODULE_DEPENDENT_MODULES])) {
                $dependencies[] = $module[KEY_MODULE_NAME];
            }
        }
    } else {
        $selfComponentName = $modules[$targetModule][KEY_MODULE_COMPONENT];
        foreach ($componentsByName[$selfComponentName][KEY_COMPONENT_MODULES] as $module) {
            if ($module != $targetModule) {
                $dependencies[] = $module;
            }
        }

        $componentDependencies = getComponentDependency(
            $selfComponentName,
            KEY_COMPONENT_DEPENDENTS
        );
        foreach ($componentDependencies as $component) {
            foreach ($componentsByName[$component][KEY_COMPONENT_MODULES] as $module) {
                if (!in_array($module, $dependencies)) {
                    $dependencies[] = $module;
                }
            }
        }
    }
    echo json_encode($dependencies, JSON_PRETTY_PRINT);
} elseif (isset($opt['list-module-dependencies'])) {
    //Get modules that depends on the specified module, directly or indirectly
    $targetModule = $opt['list-module-dependencies'];
    $dependencies = [];
    if (!isset($modules[$targetModule])) {
        die("Can't find specified module: " . $targetModule . "\n");
    }

    if ($directDependenciesOnly) {
        $module = $modules[$targetModule];
        $dependencies = $module[KEY_MODULE_DEPENDENT_MODULES];
    } else {
        $selfComponentName = $modules[$targetModule][KEY_MODULE_COMPONENT];
        foreach ($componentsByName[$selfComponentName][KEY_COMPONENT_MODULES] as $module) {
            if ($module != $targetModule) {
                $dependencies[] = $module;
            }
        }

        $componentDependencies = getComponentDependency(
            $selfComponentName,
            KEY_COMPONENT_DEPENDENCIES
        );
        foreach ($componentDependencies as $component) {
            foreach ($componentsByName[$component][KEY_COMPONENT_MODULES] as $module) {
                if (!in_array($module, $dependencies)) {
                    $dependencies[] = $module;
                }
            }
        }
    }
    echo json_encode($dependencies, JSON_PRETTY_PRINT);
}

/**
 * For a given component, return a list of components that depend on the component or a list of components
 * that the given component depends on
 *
 * @param string $component
 * @param string $direction
 * @return array
 */
function getComponentDependency($component, $direction)
{
    global $componentsByName;
    $dependencies = [];
    $queue = [];

    foreach ($componentsByName[$component][$direction] as $componentName) {
        $dependencies[] = $componentName;
        $queue[] = $componentName;
    }

    while (!empty($queue)) {
        $head = array_shift($queue);
        $headComponent = $componentsByName[$head];
        foreach ($headComponent[$direction] as $componentName) {
            if (!in_array($componentName, $dependencies)) {
                $dependencies[] = $componentName;
                $queue[] = $componentName;
            }
        }
    }

    return $dependencies;
}

/**
 * initialize the component and module dependency
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @return void
 */
function initialize()
{
    global $modules, $componentsByName;
    $baseDir = "../../";
    $fileNames = glob($baseDir . "app/code/Magento/*/composer.json");

    foreach ($fileNames as $fileName) {
        $moduleDirectoryName = basename(dirname($fileName));
        $content = json_decode(file_get_contents($fileName), true);
        if ($content[KEY_COMPOSER_COMPONENT_TYPE] != KEY_COMPOSER_MAGENTO2_MODULE) {
            continue;
        }
        $dependentModules = [];
        $dependencies = $content[KEY_COMPOSER_REQUIRE];
        foreach (array_keys($dependencies) as $name) {
            $dependentModules[$name] = $name;
        }
        $moduleName = $content[KEY_COMPOSER_COMPONENT_NAME];
        $modules[$moduleName] = [
            KEY_MODULE_DIRECTORY_NAME => $moduleDirectoryName,
            KEY_MODULE_DEPENDENT_MODULES => $dependentModules,
            KEY_MODULE_NAME => $moduleName,
        ];
    }

    //going through the array one more time to clean up the content, remove the dependencies that are not module
    foreach ($modules as &$module) {
        $dependentModules = [];
        foreach ($module[KEY_MODULE_DEPENDENT_MODULES] as $dependentModuleName) {
            if (isset($modules[$dependentModuleName])) {
                $dependentModules[$dependentModuleName] = $dependentModuleName;
            }
        }
        $module[KEY_MODULE_DEPENDENT_MODULES] = $dependentModules;
    }

    //Group strongly connected modules as components
    $components = identifyComponents($modules);

    foreach ($components as &$component) {
        if (count($component[KEY_COMPONENT_MODULES]) == 1) {
            $component[KEY_COMPONENT_NAME] = $component[KEY_COMPONENT_MODULES][0];
            $modules[$component[KEY_COMPONENT_MODULES][0]][KEY_MODULE_COMPONENT] = $component[KEY_COMPONENT_NAME];
        } elseif (in_array(KEY_MAGENTO_CORE_MODULE, $component[KEY_COMPONENT_MODULES])) {
            $component[KEY_COMPONENT_NAME] = KEY_MAGENTO_CORE_MODULE;
        } else {
            $component[KEY_COMPONENT_NAME] = implode(':', $component[KEY_COMPONENT_MODULES]);
        }
        foreach ($component[KEY_COMPONENT_MODULES] as $moduleName) {
            $modules[$moduleName][KEY_MODULE_COMPONENT] = $component[KEY_COMPONENT_NAME];
        }
        $componentsByName[$component[KEY_COMPONENT_NAME]] = $component;
    }

    //Process dependency between components
    foreach ($componentsByName as $name => &$component) {
        foreach ($component[KEY_COMPONENT_MODULES] as $moduleName) {
            foreach ($modules[$moduleName][KEY_MODULE_DEPENDENT_MODULES] as $dependentModule) {
                $dependentComponent = $modules[$dependentModule][KEY_MODULE_COMPONENT];

                if ($dependentComponent != $component[KEY_COMPONENT_NAME]) {
                    $component[KEY_COMPONENT_DEPENDENCIES][$dependentComponent] =
                        $dependentComponent;
                    $componentsByName[$dependentComponent][KEY_COMPONENT_DEPENDENTS][$component[KEY_COMPONENT_NAME]] =
                        $component[KEY_COMPONENT_NAME];
                }
            }
        }
    }
    foreach ($componentsByName as &$component) {
        if (!isset($component[KEY_COMPONENT_DEPENDENTS])) {
            $component[KEY_COMPONENT_DEPENDENTS] = [];
        }
        if (!isset($component[KEY_COMPONENT_DEPENDENCIES])) {
            $component[KEY_COMPONENT_DEPENDENCIES] = [];
        }
    }
}

/**
 * For a given acyclic graph of components, sort the components according to the dependencies so that components
 * can only depend on components with lower index
 *
 * @param array $components
 * @return array
 */
function topologicalSort($components)
{
    $sortedComponents = [];
    $rootComponents = [];
    foreach ($components as $component) {
        if (empty($component[KEY_COMPONENT_DEPENDENCIES])) {
            $rootComponents[] = $component;
        }
    }

    while (!empty($rootComponents)) {
        $rootComponent = array_shift($rootComponents);
        $sortedComponents[] = $rootComponent[KEY_COMPONENT_NAME];

        foreach ($rootComponent[KEY_COMPONENT_DEPENDENTS] as $componentName) {
            unset($components[$componentName][KEY_COMPONENT_DEPENDENCIES][$rootComponent[KEY_COMPONENT_NAME]]);
            if (empty($components[$componentName][KEY_COMPONENT_DEPENDENCIES])) {
                $rootComponents[] = $components[$componentName];
            }
        }
    }

    return $sortedComponents;
}

/**
 * Identify components in the dependency graph using Tarjan algorithm. Modules that circularly depend on each other
 * are grouped into components. Modules that are not in a cyclic graph are grouped into component of its own
 *
 * @param array $modules
 * @return array
 */
function identifyComponents($modules)
{
    $index = 0;
    $stack = [];
    $components = [];

    foreach ($modules as &$module) {
        if (!isset($module['index'])) {
            identifyComponent($modules, $module, $stack, $index, $components);
        }
    }

    return $components;
}

/**
 * Recursive function to identify one component
 *
 * @param array $modules
 * @param array $module
 * @param array $stack
 * @param int $index
 * @param array $components
 * @return void
 */
function identifyComponent(&$modules, &$module, &$stack, &$index, &$components)
{
    $module['index'] = $index;
    $module['lowlink'] = $index;
    $index++;
    $stack[] = $module[KEY_MODULE_NAME];

    foreach ($module[KEY_MODULE_DEPENDENT_MODULES] as $dependentModuleName) {
        $dependentModule = &$modules[$dependentModuleName];
        if (!isset($dependentModule['index'])) {
            identifyComponent($modules, $dependentModule, $stack, $index, $components);
            $module['lowlink'] = min($module['lowlink'], $dependentModule['lowlink']);
        } elseif (in_array($dependentModuleName, $stack)) {
            $module['lowlink'] = min($module['lowlink'], $dependentModule['index']);
        }
    }

    if ($module['lowlink'] == $module['index']) {
        $component = [];
        do {
            $moduleName = array_pop($stack);
            $component[KEY_COMPONENT_MODULES][] = $moduleName;
        } while ($moduleName != $module[KEY_MODULE_NAME]);
        $components[] = $component;
    }
}
