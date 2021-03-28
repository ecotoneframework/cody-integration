<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code\NodeVisitor;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

final class PhpParserClassMethod extends NodeVisitorAbstract
{
    private ClassMethod $classMethod;

    public function __construct(ClassMethod $classMethod)
    {
        $this->classMethod = $classMethod;
    }

    public function afterTraverse(array $nodes): ?array
    {
        $newNodes = [];

        foreach ($nodes as $node) {
            $newNodes[] = $node;

            if ($node instanceof Namespace_) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Class_) {
                        if ($this->checkMethodExists($stmt)) {
                            return null;
                        }
                        $stmt->stmts[] = $this->classMethod;
                    }
                }
            } elseif ($node instanceof Class_) {
                if ($this->checkMethodExists($node)) {
                    return null;
                }
                $node->stmts[] = $this->classMethod;
            }
        }

        return $newNodes;
    }

    private function checkMethodExists(Class_ $node): bool
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod
                && $stmt->name->name === $this->classMethod->name->name
            ) {
                return true;
            }
        }

        return false;
    }
}
