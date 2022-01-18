<?php

if (file_exists(__DIR__ . '/../lib/token.json')) {
    die("Your messages will be parsed soon :)");
}

header('Location: /');
