<?php
if ($a) {
    echo 'a';
} elseif (!$b) {
    echo '!b';
} elseif (~$b) {
    echo '~b';
} elseif ($c || $d) {
    echo 'cd';
} else {
    echo 'e';
}
