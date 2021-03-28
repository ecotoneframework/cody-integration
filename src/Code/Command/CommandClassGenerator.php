<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code\Command;

use Ecotone\InspectioCody\Code\FileLoader;
use Ecotone\InspectioCody\Code\Filesystem;
use Ecotone\InspectioCody\Code\Printer\PrettyPrinter;
use Ecotone\InspectioCody\Code\ValueObject\ValueObjectGenerator;
use Ecotone\InspectioCody\Config\Command;
use Ecotone\InspectioCody\Context;
use Ecotone\InspectioCody\Metadata\CommandMetadata;
use Ecotone\InspectioCody\Metadata\HasSchema;
use EventEngine\InspectioGraph\VertexType;
use EventEngine\InspectioGraphCody as Cody;
use OpenCodeModeling\JsonSchemaToPhp\Type\Type;
use PhpParser\Parser;

final class CommandClassGenerator
{
    private Command $config;
    private Parser $parser;
    private PrettyPrinter $printer;
    private Cody\EventSourcingAnalyzer $eventSourcingAnalyzer;
    private FileLoader $fileLoader;
    private ValueObjectGenerator $valueObjectGenerator;
    private Filesystem $filesystem;

    public function __construct(
        Command $config,
        Parser $parser,
        PrettyPrinter $printer,
        Cody\EventSourcingAnalyzer $eventSourcingAnalyzer,
        FileLoader $fileLoader,
        ValueObjectGenerator $valueObjectGenerator,
        Filesystem $filesystem
    ) {
        $this->config = $config;
        $this->parser = $parser;
        $this->printer = $printer;
        $this->eventSourcingAnalyzer = $eventSourcingAnalyzer;
        $this->fileLoader = $fileLoader;
        $this->valueObjectGenerator = $valueObjectGenerator;
        $this->filesystem = $filesystem;
    }

    public function generate(
        VertexType $command,
        CommandMetadata $metadata,
        Context $ctx
    ): array {
        $commandName = ($this->config->getFilterClassName())($command->label());
        $commandPath = $this->config->determinePath($command, $this->eventSourcingAnalyzer);

        $metadata = $command->metadataInstance();

        if(!$metadata instanceof HasSchema) {
            throw new \RuntimeException("Wrong VertexType passed to "  . __METHOD__ . ". Expected a command with JSONSchema metadata.");
        }

        $typeSet = Type::fromDefinition($metadata->schema());

        $classBuilderCollection = $this->valueObjectGenerator->generateObject(
            $commandPath,
            $commandName,
            $typeSet,
            $ctx
        );

        $this->valueObjectGenerator->addGetterMethods($classBuilderCollection);

        return $this->valueObjectGenerator->generateFiles($classBuilderCollection);
    }
}
