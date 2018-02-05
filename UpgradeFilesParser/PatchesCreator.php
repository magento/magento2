<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * A custom "Import" adapter for Magento_ImportExport module that allows generating arbitrary data rows
 */

namespace Magento\Setup\Model;

class PatchesCreator
{
    private $patchCreatorsPath = __DIR__ . "/patch_template.php.dist";

    private $methodsPath = __DIR__ . "/method_template.php.dist";

    private $classVariable = '/\$this->([\w\d]+)([^\(])*/';

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    public function __construct()
    {
        ini_set('xdebug.max_nesting_level', 1002000);
    }

    private function addUsesFromArguments($filePath, $namspace)
    {
        $files = glob($filePath . "*.php");
        $classes = [];
        foreach ($files as $file) {
            $file = str_replace(".php", "", $file);
            $file = explode("/", $file);
            $file = array_pop($file);
            $classes[] = "use " . $namspace . "\\" . $file;
        }

        return $classes;
    }

    public function createPatchFromFile($path, $file, &$currentNumber)
    {
        if (!file_exists($path . "/" . $file)) {
            return;
        }
        $mode = strpos($file, "Up") !== false ? "UpgradeData" : "InstallData";
        $method = strpos($file, "Up") !== false ? "upgrade" : "install";
        /** @var DataFilesParser $parser */
        $parser = new DataFilesParser();
        $fileDesscriptor = fopen($path . "/" . $file, 'r');
        $patches = [];

        $parser->parse($result, $fileDesscriptor);
        $uses = $this->parseUses($result);
        $upgradeFunction = $result[$mode]['data'][$method]['data'];

        if ($mode === 'UpgradeData') {
            $mandatoryCodeBefore = $this->getMandatoryCodeBefore($upgradeFunction);
            $mandatoryCodeAfter = $this->getMandatoryCodeAfter($upgradeFunction);
        } else {
            $mandatoryCodeBefore = [];
            $mandatoryCodeAfter = [];
        }

        $constructor = isset($result[$mode]['data']['__construct']) ? $result[$mode]['data']['__construct'] : [];
        $cData = $this->implementConstructor($mandatoryCodeBefore, $constructor);
        $cData = array_merge_recursive($cData, $this->implementConstructor($mandatoryCodeAfter, $constructor));

        if ($mode == 'InstallData') {
            $constructorData = $this->implementConstructor($upgradeFunction, $constructor);
            $patchCompile = [
                'codeBefore' => implode("", $mandatoryCodeBefore),
                'code' => implode("", $this->codeFormatter($upgradeFunction)),
                'codeAfter' => implode("", $mandatoryCodeAfter),
                'c_head' => $cData['c_head'],
                'c_body' => $cData['c_body'],
                'uses' => $uses,
                'constructor' => $constructor,
                'constants' => $this->implementConstants($result[$mode]['data']),
                'namespace' => $this->findNamespace($result),
                'additional_information' => $this->getAddtionalInformation($upgradeFunction, $result[$mode]['data'])
            ];

            $constructorAdditonalData = $this->implementConstructor($upgradeFunction, $constructor);
            $patchCompile = array_replace_recursive($patchCompile, $constructorAdditonalData);
            $patches["Initial"] = array_replace_recursive(
                $patchCompile,
                $constructorData
            );
        }

        foreach ($upgradeFunction as $key => $line) {
            if (is_array($line)) {

                $constructorData = $this->implementConstructor($line['data'], $constructor);
                $patchCompile = [
                    'codeBefore' => implode("", $mandatoryCodeBefore),
                    'code' => implode("", $this->codeFormatter($line['data'])),
                    'codeAfter' => implode("", $mandatoryCodeAfter),
                    'c_head' => $cData['c_head'],
                    'c_body' => $cData['c_body'],
                    'uses' => $uses,
                    'constructor' => $constructor,
                    'constants' => $this->implementConstants($result[$mode]['data']),
                    'namespace' => $this->findNamespace($result),
                    'additional_information' => $this->getAddtionalInformation($line['data'], $result[$mode]['data'])
                ];

                $constructorAdditonalData = $this->implementConstructor($line['data'], $constructor);
                $patchCompile = array_replace_recursive($patchCompile, $constructorAdditonalData);
                $patches[$this->getPatchVersion($key)] = array_replace_recursive(
                    $patchCompile,
                    $constructorData
                );
            }
        }

        $classNames = [];
        foreach ($patches as $key => $patch) {
            $classNames[] = $this->_createPatch($patch, $key, $path);
        }

        $etcFolder = str_replace("Setup/", "etc/", $path);
        $this->publishPatchXml($etcFolder, $classNames, $currentNumber);
        return $classNames;
    }

    private function implementConstructor($code, array $constructor)
    {
        $constructorDependecies = [];
        $constructorBody = [];

        foreach ($this->codeFormatter($code) as $line) {
            if (is_array($line)) {
                continue;
            }
            if (preg_match($this->classVariable, $line, $matches)) {
                $variable = $matches[1];

                if (isset($constructor['arguments'])) {
                    foreach ($constructor['arguments'] as $constructorInjection) {
                        if (strpos($constructorInjection, $variable) !== false) {
                            $constructorDependecies[] = $constructorInjection;

                            foreach ($constructor['data'] as $constructorVarDeclaration) {
                                if (strpos($constructorVarDeclaration, $variable) !== false) {
                                    $constructorBody[] = $constructorVarDeclaration;
                                }
                            }
                        }
                    }
                }
            }
        }
        $variables = [];
        foreach ($constructorDependecies as $dependecy) {
            $variableName = explode(" $", $dependecy)[1];
            $variableType = explode(" $", $dependecy)[0];
            $variableType = rtrim(ltrim($variableType));
            $variableName = preg_replace('/\n\s{2,}/', '', $variableName);
            $annotation = "
    /**
    * @param %s $%s
    */
";
            $annotation = sprintf($annotation, $variableType, $variableName);
            $variableName = sprintf('private $%s;', $variableName);
            $variableName = $annotation . "    " . $variableName;
            $variables[] = $variableName;
        }

        return [
            "c_head" => $constructorDependecies, "c_body" => $constructorBody, "c_variables" => $variables
        ];
    }

    private function getAddtionalInformation($code, array $class)
    {
        $methods = [];
        foreach ($this->codeFormatter($code) as $line) {
            if (is_array($line)) {
                continue;
            }
            if (preg_match($this->classVariable, $line, $matches)) {
                $depndency = $matches[1];
                if (isset($class[$depndency])) {
                    $methods[$depndency]['code'] = $class[$depndency]['data'];
                    $methods[$depndency]['arguments'] = isset($class[$depndency]['arguments']) ? $class[$depndency]['arguments'] : [];
                    $methods = array_merge($methods, $this->getAddtionalInformation($class[$depndency]['data'], $class));
                }
            }
        }

        return $methods;
    }

    private function codeFormatter($code)
    {
        $isEmptyLine = false;
        $formattedCode = [];

        foreach ($code as $line) {
            if ($this->isEmptyLine($line)) {
                if ($isEmptyLine) {
                    continue;
                }

                $isEmptyLine = true;
            }
            $formattedCode[] = $line;
        }

        return $formattedCode;
    }

    private function isEmptyLine($line)
    {
        return $line === "\n";
    }

    private function parseUses($result)
    {
        $uses = "";
        foreach ($result as $item) {
            if (is_string($item) && strpos($item, "use") === 0) {
                $uses .= $item;
            }
        }

        return $uses;
    }

    private function publishPatchXml($etcFolder, array $classNames, &$increment)
    {
        $dataNode = new \SimpleXMLElement("<data></data>");
        $patchesNode = $dataNode->addChild('patches');

        if (file_exists($etcFolder . "/patch.xml")) {
            $data = new \SimpleXMLElement(file_get_contents($etcFolder . "/patch.xml"));
            $patches = $data->xpath("//patch");

            foreach ($patches as $oldPatch) {
                $attributes = $oldPatch->attributes();
                if (!in_array($attributes['name'], $classNames)) {
                    $patch = $patchesNode->addChild('patch');
                    $patch->addAttribute('name', $attributes['name']);
                    $patch->addAttribute('sortOrder', $attributes['sortOrder']);
                }
            }
        }

        foreach ($classNames as $name) {
            $patch = $patchesNode->addChild('patch');
            $patch->addAttribute('name', $name);
            $patch->addAttribute('sortOrder', $increment++);
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($dataNode->asXML());
        $dom->save($etcFolder . "/patch.xml");
    }

    private function _createPatch(array $patch, $key, $filePath)
    {
        $code = $patch['codeBefore'] . $patch["code"] . $patch["codeAfter"];
        $templateData = file_get_contents($this->patchCreatorsPath);
        $class = sprintf("Patch%s", $key);
        $additionalFunctions = [];
        $cHead = $patch['c_head'];
        $cBody = $patch['c_body'];
        $cVariables = $patch['c_variables'];
        $constructor = $patch['constructor'];
        $additionalUses = implode(";\n", $this->addUsesFromArguments($filePath, $patch['namespace']));
        $uses = $additionalUses . "\n" . $patch['uses'];
        $namepsace = $patch["namespace"] . "\\Patch";
        $result = str_replace("%namespace%", $namepsace, $templateData);
        $result = str_replace("%class%", $class, $result);
        $result = str_replace("%code%", $code, $result);
        $result = str_replace("%uses%", $uses, $result);

        if (is_array($patch['additional_information'])) {
            foreach ($patch['additional_information'] as $method => $methodData) {
                $additionalContent = file_get_contents($this->methodsPath);
                $additionalContent = rtrim($additionalContent);
                $additionalContent = str_replace("%method%", $method, $additionalContent);
                $additionalContent = str_replace("%arguments%", implode(", ", $methodData['arguments']), $additionalContent);
                $additionalContent = str_replace("%method_body%", implode("", $methodData['code']), $additionalContent);
                $cData = $this->implementConstructor($methodData['code'], $constructor);
                $cHead = array_replace_recursive($cHead, $cData['c_head']);
                $cBody = array_replace_recursive($cBody, $cData['c_body']);
                $cVariables = array_replace_recursive($cVariables, $cData['c_variables']);
                $additionalFunctions[] = $additionalContent;
            }
        }
        $constructorResult = "";
        if (!empty($cHead)) {
            $constructorResult = file_get_contents(__DIR__ . "/constructor_template.php.dist");

            $lastDependency = array_pop($cHead);
            $lastDependency = preg_replace("/^(.*)(\\n\\s*)$/", "$1", $lastDependency);
            $cHead[] = $lastDependency;
            $cParams = [];
            foreach ($cHead as $injection) {
                $cParams[] = '@param ' . rtrim(ltrim($injection));
            }

            $cHead = rtrim(implode(", ", $cHead));
            $cHead = ltrim($cHead);
            $cBody = rtrim(implode("", $cBody));

            $constructorResult = str_replace("%c_head%", $cHead, $constructorResult);
            $constructorResult = str_replace("%c_body%", $cBody, $constructorResult);
            $constructorResult = str_replace("%dep%", implode("", $cParams), $constructorResult);
        }


        $result = str_replace("%constructor%", $constructorResult, $result);
        $result = str_replace("%constants%", implode("", $patch['constants']), $result);
        $result = str_replace("%variables%", implode("", $cVariables), $result);
        $result = str_replace("%additional_functions%", implode("", $additionalFunctions), $result);
        $filePath = $filePath . "/" . "Patch";
        if (!is_dir($filePath)) {
            mkdir($filePath);
        }

        file_put_contents(sprintf("%s/%s.php", $filePath, $class), $result);

        return $namepsace . "\\" . $class;
    }

    private function findNamespace(array $code)
    {
        foreach ($code as $line) {
            if (strpos($line, "namespace") !== false) {
                $line = str_replace("\n", "", $line);
                $line = str_replace("namespace ", "", $line);
                return str_replace(";", "", $line);
            }
        }

        throw new \Exception("Cannot find namespace");
    }

    private function getPatchVersion($patchKey)
    {
        return str_replace(".", "", $patchKey);
    }

    private function implementConstants(array $class)
    {
        $constants = [];

        foreach ($class as $line) {
            if (is_string($line) && preg_match("/const\\s.*/", $line)) {
                $constants[] = $line;
            }
        }

        return $constants;
    }


    private function getMandatoryCodeBefore(array &$function)
    {
        $mandatoryCode = [];

        foreach ($function as $key => $line) {
            if (is_string($line)) {
                $mandatoryCode[] = $line;
                unset($function[$key]);
            } elseif (is_array($line)) {
                break;
            }
        }

        return $mandatoryCode;
    }

    private function getMandatoryCodeAfter(array &$function)
    {
        $mandatoryCode = [];

        foreach ($function as $key => $line) {
            if (is_string($line)) {
                $mandatoryCode[] = $line;
                unset($function[$key]);
            }
        }

        return $mandatoryCode;
    }
}
