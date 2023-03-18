<?php
declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PSR12' => true,
    'strict_param' => true,
    'array_syntax' => ['syntax' => 'short'],
];

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor'
    ])
    ->ignoreDotFiles(true)
    ->ignoreVCSIgnored(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder($finder);
