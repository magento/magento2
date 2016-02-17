<?php

namespace League\CLImate\TerminalObject\Basic;

interface BasicTerminalObjectInterface
{
    public function result();

    public function settings();

    /**
     * @return void
     */
    public function importSetting( $setting );

    /**
     * @return boolean
     */
    public function sameLine();

}
