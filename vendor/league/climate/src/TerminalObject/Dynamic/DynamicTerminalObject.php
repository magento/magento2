<?php

namespace League\CLImate\TerminalObject\Dynamic;

use League\CLImate\Decorator\Parser\ParserImporter;
use League\CLImate\Settings\SettingsImporter;
use League\CLImate\Util\OutputImporter;
use League\CLImate\Util\UtilImporter;

/**
 * The dynamic terminal object doesn't adhere to the basic terminal object
 * contract, which is why it gets its own base class
 */

abstract class DynamicTerminalObject
{
    use SettingsImporter, ParserImporter, OutputImporter, UtilImporter;
}
