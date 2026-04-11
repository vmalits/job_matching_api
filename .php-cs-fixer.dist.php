<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arguments', 'arrays', 'match', 'parameters']],
        'declare_strict_types' => true,
        'strict_param' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'no_superfluous_phpdoc_tags' => true,
        'blank_line_between_import_groups' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
