<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code;

use PhpParser\Node;
use PhpParser\Parser;

final class LocalSystemFileLoader implements FileLoader
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string $filename
     * @return Node\Stmt[]
     */
    public function loadAstFromFile(string $filename): array
    {
        $code = '';

        if (\file_exists($filename) && \is_readable($filename)) {
            $code = \file_get_contents($filename);
        }

        $ast = $this->parser->parse($code);

        if (!$ast) {
            return [];
        }

        return $ast;
    }
}
