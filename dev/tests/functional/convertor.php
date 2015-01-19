<?php

foreach (glob("/var/www/magento-ce.localhost/dev/tests/functional/tests/app/Magento/*/Test/etc/scenario.xml") as $filename) {
    $data = file_get_contents($filename);
    $xml = simplexml_load_string($data);
    $newXml = new SimpleXMLElement("<scenarios xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"../../../../../../vendor/magento/mtf/Mtf/Config/etc/test_scenario.xsd\"></scenarios>");
    foreach($xml->children() as $scenario) {
        $newScenario = $newXml->addChild('scenario');
        foreach($scenario->attributes() as $scenarioAttributeCode => $scenarioAttributeValue) {
            if (in_array($scenarioAttributeCode, ['name'])) {
                $newScenario->addAttribute($scenarioAttributeCode, $scenarioAttributeValue);
            }
        }
        foreach ($scenario->children()[0] as $methodXml) {
            foreach ($methodXml->children()[0] as $stepXml) {
                $stepName = (string)$stepXml->attributes()->name;
                if ($stepName) {
                    $newStep = $newScenario->addChild('step');
                    $newStep->addAttribute('name', (string)$stepXml->attributes()->name);
                    if ($stepXml->children()->next) {
                        $newStep->addAttribute('next', (string)$stepXml->children()->next);
                    }
                    if ($stepXml->children()->prev) {
                        $newStep->addAttribute('prev', (string)$stepXml->children()->prev);
                    }
                    foreach ($stepXml->children()->arguments as $arguments) {
                        if (!empty($arguments[0])) {
                            foreach ($arguments[0] as $key => $val) {
                                if (count($val->children()) == 0) {
                                    $newItem = $newStep->addChild('item');
                                    $newItem->addAttribute('name', $arguments->children()->attributes()->name);
                                    $newItem->addAttribute('value', $val);
                                } else {
                                    foreach ($val->children() as $subItem) {
                                        $newItem = $newStep->addChild('item');
                                        $newItem->addAttribute('name',
                                            $arguments->children()->attributes()->name . '/'
                                            . (string)$subItem->attributes()->name);
                                        $newItem->addAttribute('value', $subItem);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $newScenario->addAttribute('firstStep', (string)$stepXml);
                }
            }
        }
        $path = dirname($filename) . '/test_scenario.xml';
        $dom = dom_import_simplexml($newXml)->ownerDocument;
        $dom->formatOutput = true;
        file_put_contents($path, $dom->saveXML());
    }
}
