<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SanityTest extends PHPUnit_Framework_TestCase
{
    function testCodeFiles_allOmitPHPCloseTag()
    {
        $codeFiles = explode("\n", shell_exec("find ./lib -name \*.php"));
        foreach ($codeFiles as $codeFile) {
            if ($codeFile == "") continue;
            $code = file_get_contents($codeFile);
            $this->assertNotContains("?>", $code, "$codeFile should not contain a PHP close tag");
        }
    }
}
