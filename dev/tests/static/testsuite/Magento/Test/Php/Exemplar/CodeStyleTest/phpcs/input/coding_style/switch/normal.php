<?php
for ($i = 0; $i < 10; $i++) {
    switch ($i) {
        case 0:
            echo '0';
            break;
        case 1:
        case 2:
            echo '12';
            break;
        case 3:
            echo '3';
        case 4:
            echo '34';
            break;
        default:
            break;
    }
}
