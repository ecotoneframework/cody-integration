<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code\Printer;

use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;

final class CsPrinter implements PrettyPrinter
{
    private PrettyPrinterAbstract $prettyPrinter;
    private PhpCodeSniffer $codeSniffer;

    /**
     * CsPrinter constructor.
     * @param PrettyPrinterAbstract $prettyPrinter
     * @param PhpCodeSniffer $codeSniffer
     */
    public function __construct(PrettyPrinterAbstract $prettyPrinter, PhpCodeSniffer $codeSniffer)
    {
        $this->prettyPrinter = $prettyPrinter;
        $this->codeSniffer = $codeSniffer;
    }

    public function prettyPrintFile(Node ...$nodes): string
    {
        $code = $this->prettyPrinter->prettyPrintFile($nodes);

        return $this->codeSniffer->process($code);
    }

    public function applyCodeStyle(string $code): string
    {
        return $this->codeSniffer->process($code);
    }

    public function getPrettyPrinter(): PrettyPrinterAbstract
    {
        return $this->prettyPrinter;
    }
}
