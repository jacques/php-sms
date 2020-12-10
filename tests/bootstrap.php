<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

\VCR\VCR::configure()
    ->enableRequestMatchers(['method', 'url', 'host']);
