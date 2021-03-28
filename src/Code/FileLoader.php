<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code;

use PhpParser\Node;

interface FileLoader
{
    /**
     * @param string $filename
     * @return Node\Stmt[]
     */
    public function loadAstFromFile(string $filename): array;
}
