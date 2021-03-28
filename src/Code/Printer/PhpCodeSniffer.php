<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code\Printer;

\define('PHP_CODESNIFFER_VERBOSITY', 0);
\define('PHP_CODESNIFFER_CBF', true);

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Util\Tokens;

final class PhpCodeSniffer
{
    private Config $config;

    private Ruleset $ruleSet;

    public function __construct(Config $config)
    {
        Tokens::$arithmeticTokens; // needed as workaround for error: Use of undefined constant T_CLOSURE
        $this->config = $config;
        $this->ruleSet = new Ruleset($config);
    }

    public function process(string $code): string
    {
        $file = new DummyFile($code, $this->ruleSet, $this->config);
        $file->process();
        $file->fixer->fixFile();

        return $file->fixer->getContents();
    }
}
