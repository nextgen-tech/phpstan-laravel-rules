<?php
declare(strict_types = 1);

namespace NGT\PHPStan\Rules;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class ForbiddenMethodsInCode implements Rule
{
    protected const FORBIDDEN_METHODS = [
        'dd',
        'dump',
        'debug',
    ];

    /** @var \PHPStan\Rules\RuleLevelHelper */
    private $ruleLevelHelper;

    public function __construct(
        RuleLevelHelper $ruleLevelHelper
    ) {
        $this->ruleLevelHelper = $ruleLevelHelper;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $methodName = (string) $node->name;

        $typeResult = $this->ruleLevelHelper->findTypeToCheck(
            $scope,
            $node->var,
            sprintf('Call to method %s() on an unknown class %%s.', $methodName),
            static function (Type $type) use ($methodName): bool {
                return $type->canCallMethods()->yes() && $type->hasMethod($methodName)->yes();
            }
        );

        $varType = $typeResult->getType();

        if ($varType instanceof ErrorType) {
            return $typeResult->getUnknownClassErrors();
        } elseif (!$varType instanceof ObjectType) {
            return [];
        }

        $varClassName = $varType->getClassName();

        if ($this->classIsInstanceOfCollection($varClassName) && $this->methodIsForbidden($methodName)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Usage of method %s::%s() in production code is forbidden.',
                    $varClassName,
                    $methodName
                ))->line(
                    $node->getEndLine()
                )->build(),
            ];
        }

        return [];
    }

    protected function classIsInstanceOfCollection(string $class): bool
    {
        if (class_exists(Collection::class)) {
            return is_a($class, Collection::class, true);
        }
    }

    protected function methodIsForbidden(string $method): bool
    {
        return in_array($method, static::FORBIDDEN_METHODS, true);
    }
}
