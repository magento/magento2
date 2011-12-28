<?php

abstract class Foo
{
    /**
     * Method that violates the allowed cyclomatic complexity
     */
    public function bar()
    {
        $one = $two = $three = $four = $five = $six = $seven = $eight = $nine = $ten = $eleven = $twelve = 0;
        if ($one == $two) { // 1
            if ($three == $four) { // 2
                $this->stub();
            } else if ($five == $six) { // 3
                $this->stub();
            }  else {
                $this->stub();
            }
        } else if ($seven == $eight) { // 4
            while ($seven == $eight) { // 5
                $this->stub();
            }
        } else if ($nine == $ten) { // 6
            for ($n = 0; $n < $eleven; $n++) { // 7
                $this->stub();
            }
        } else {
            switch ($twelve) {
                case 1: // 8
                    $this->stub();
                    break;
                case 2: // 9
                    $this->stub();
                    break;
                default: // 10
                    $this->stub();
                    break;
            }
        }
    }

    abstract public function stub();
}
