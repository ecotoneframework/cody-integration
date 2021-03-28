<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code\Printer;

use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;

interface PrettyPrinter
{
    public function prettyPrintFile(Node ...$nodes): string;

    public function applyCodeStyle(string $code);

    public function getPrettyPrinter(): PrettyPrinterAbstract;
}
