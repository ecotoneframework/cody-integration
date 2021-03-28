<?php

declare(strict_types=1);

namespace Ecotone\InspectioCody;

use Ecotone\InspectioCody\Code\Filesystem;
use Ecotone\InspectioCody\Code\Printer\CsPrinter;
use Ecotone\InspectioCody\Code\Printer\PhpCodeSniffer;
use Ecotone\InspectioCody\Code\Printer\PrettyPrinter;
use Ecotone\InspectioCody\Code\Printer\Standard;
use PHP_CodeSniffer\Config;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class Context
{
    public string $serviceName;
    public string $sharedValueObjectFolder;
    public string $voConverterFolder;

    public Parser $parser;
    public PrettyPrinter $printer;
    public Filesystem $filesystem;

    public function __construct(
        string $serviceName,
        string $sharedValueObjectFolder,
        string $voConverterFolder,
        Filesystem $filesystem,
        array $csConfig = []
    ) {
        $this->serviceName = $serviceName;
        $this->sharedValueObjectFolder = $sharedValueObjectFolder;
        $this->voConverterFolder = $voConverterFolder;
        $this->filesystem = $filesystem;
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->printer = new CsPrinter(
            new Standard(['shortArraySyntax' => true]),
            new PhpCodeSniffer(new Config(array_merge(['inline'], $csConfig), false))
        );
    }
}
