<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Config;

use Ecotone\InspectioCody\Code\FileLoader;
use Ecotone\InspectioCody\Code\ValueObject\ValueObjectGenerator;
use Ecotone\InspectioCody\Context;
use EventEngine\InspectioGraph\EventSourcingAnalyzer;
use EventEngine\InspectioGraph\VertexType;
use OpenCodeModeling\CodeAst\Package\ClassInfoList;
use PhpParser\Parser;
use PhpParser\PrettyPrinterAbstract;

interface Command
{
    public function getBasePath(): string;

    public function getClassInfoList(): ClassInfoList;

    public function getFilterAggregateFolder(): ?callable;

    public function getFilterClassName(): callable;

    public function getFilterConstName(): callable;

    public function getFilterConstValue(): callable;

    public function getFilterDirectoryToNamespace(): callable;

    public function getFilterNamespaceToDirectory(): callable;

    public function getFilterPropertyName(): callable;

    public function getFilterMethodName(): callable;

    public function getParser(): Parser;

    public function getPrinter(): PrettyPrinterAbstract;

    public function getValueObjectGenerator(Context $ctx, FileLoader $fileLoader): ValueObjectGenerator;

    public function determineValueObjectPath(VertexType $type, EventSourcingAnalyzer $analyzer): string;

    public function determinePath(VertexType $type, EventSourcingAnalyzer $analyzer): string;
}
