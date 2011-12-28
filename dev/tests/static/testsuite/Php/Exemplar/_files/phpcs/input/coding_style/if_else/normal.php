<?php
if ($a) {
    echo 'a';
} else if (!$b) {
    echo '!b';
} else if (~$b) {
    echo '~b';
} else if ($c || $d) {
    echo 'cd';
} else {
    echo 'e';
}
