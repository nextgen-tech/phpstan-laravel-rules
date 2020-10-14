<?php
declare(strict_types = 1);

namespace NGT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

final class ForbiddenFunctionsInCode implements Rule
{
    protected const FORBIDDEN_FUNCTIONS = [
        'dd',
        'ddd',
        'dump',
        'debug',
        'print_r',
        'var_dump',
        'var_export',
    ];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Name) {
            return [];
        }

        $functionName = (string) $node->name;

        if (in_array($functionName, static::FORBIDDEN_FUNCTIONS, true)) {
            return [sprintf('Usage of function %s() in production code is forbidden.', $functionName)];
        }

        return [];
    }
}
