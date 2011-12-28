<?php

class Foo
{
    public function loopArrayCallback(array $array, $callback)
    {
        $index = 0;
        while (true) {
            if ($index >= count($array)) {
                goto end;
            }
            $callback($array[$index]);
            $index++;
        }
        end:
    }
}
