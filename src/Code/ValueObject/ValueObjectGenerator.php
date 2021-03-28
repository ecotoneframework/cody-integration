<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code\ValueObject;

use Ecotone\InspectioCody\Code\FileLoader;
use Ecotone\InspectioCody\Code\NodeVisitor\PhpParserClassMethod;
use Ecotone\InspectioCody\Code\Printer\PrettyPrinter;
use Ecotone\InspectioCody\Context;
use OpenCodeModeling\CodeAst\Builder\ClassBuilder;
use OpenCodeModeling\CodeAst\Builder\ClassMethodBuilder;
use OpenCodeModeling\CodeAst\Builder\ClassPropertyBuilder;
use OpenCodeModeling\CodeAst\Builder\File;
use OpenCodeModeling\CodeAst\Builder\FileCollection;
use OpenCodeModeling\CodeAst\Builder\ParameterBuilder;
use OpenCodeModeling\CodeAst\Code\BodyGenerator;
use OpenCodeModeling\CodeAst\Code\ClassConstGenerator;
use OpenCodeModeling\CodeAst\FileCodeGenerator;
use OpenCodeModeling\CodeAst\Package\ClassInfo;
use OpenCodeModeling\CodeAst\Package\ClassInfoList;
use OpenCodeModeling\JsonSchemaToPhpAst\ValueObjectFactory;
use OpenCodeModeling\JsonSchemaToPhp\Type\CustomSupport;
use OpenCodeModeling\JsonSchemaToPhp\Type\TypeDefinition;
use OpenCodeModeling\JsonSchemaToPhp\Type\TypeSet;
use PhpParser\BuilderFactory;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use function array_map;
use function array_values;
use function str_replace;

final class ValueObjectGenerator
{
    private PrettyPrinter $printer;

    private FileLoader $fileLoader;

    private ClassInfoList $classInfoList;

    private ValueObjectFactory $valueObjectFactory;

    private FileCodeGenerator $fileGenerator;

    /**
     * @var callable
     */
    private $filterPropertyName;

    /**
     * @var callable
     */
    private $filterClassName;

    public function __construct(
        PrettyPrinter $printer,
        FileLoader $fileLoader,
        ClassInfoList $classInfoList,
        ValueObjectFactory $valueObjectFactory,
        FileCodeGenerator $fileGenerator,
        callable $filterPropertyName,
        callable $filterClassName
    ) {
        $this->printer = $printer;
        $this->fileLoader = $fileLoader;
        $this->classInfoList = $classInfoList;
        $this->valueObjectFactory = $valueObjectFactory;
        $this->fileGenerator = $fileGenerator;
        $this->filterPropertyName = $filterPropertyName;
        $this->filterClassName = $filterClassName;
    }

    public function sortThingsForCodeSniffer(FileCollection $fileCollection): void
    {
        $sort = static function (string $a, string $b) {
            return $a <=> $b;
        };

        foreach ($fileCollection as $classBuilder) {
            if ($classBuilder instanceof ClassBuilder) {
                $classBuilder->sortTraits($sort);
            }
            $classBuilder->sortNamespaceImports($sort);
        }
    }

    public function generateFile(File $classBuilder): array
    {
        return $this->generateFiles(FileCollection::fromItems($classBuilder))[0];
    }

    public function generateFiles(FileCollection $fileCollection): array
    {
        $files = [];

        $this->sortThingsForCodeSniffer($fileCollection);

        $currentFileAst = function (File $classBuilder, ClassInfo $classInfo) {
            $path = $classInfo->getPath($classBuilder->getNamespace() . '\\' . $classBuilder->getName());
            $filename = $classInfo->getFilenameFromPathAndName($path, $classBuilder->getName());

            return $this->fileLoader->loadAstFromFile($filename);
        };

        foreach ($this->fileGenerator->generateFiles($fileCollection, $currentFileAst) as $filename => $code) {
            $files[] = [
                'filename' => $filename,
                'code' => $this->printer->applyCodeStyle($code),
            ];
        }

        return $files;
    }

    public function addConstants(FileCollection $fileCollection, int $visibility = ClassConstGenerator::FLAG_PUBLIC): void
    {
        $this->valueObjectFactory->addClassConstantsForProperties(
            $fileCollection,
            $visibility
        );
    }

    public function addGetterMethods(FileCollection $fileCollection): void
    {
        $this->valueObjectFactory->addGetterMethodsForProperties(
            $fileCollection,
            true
        );
    }

    public function addConverters(FileCollection $fileCollection, Context $ctx): void
    {
        $converterClassInfo = $this->classInfoList->classInfoForPath($ctx->voConverterFolder);
        $converters = [];
        $phpParserFactory = new BuilderFactory();

        foreach ($fileCollection as $valueObject) {
            if (! $valueObject instanceof ClassBuilder) {
                continue;
            }

            if(!ValueType::isValueObject($valueObject)) {
                continue;
            }

            if(ValueType::analyze($valueObject) === ValueType::ASSOC) {
                // Assocs become classes and JMS converter can convert them out-of-the-box by reading prop type hints
                continue;
            }


            $converterName = $valueObject->getName() . 'Converter';

            $converter = ClassBuilder::fromScratch(
                $converterName,
                $converterClassInfo->getClassNamespaceFromPath($ctx->voConverterFolder)
                . $this->extractSubNamespaceFromSharedValueObject($valueObject, $ctx)
            )->setFinal(true)
                ->addNamespaceImport(
                    'Ecotone\Messaging\Attribute\Converter',
                    $valueObject->getNamespace() . '\\' . $valueObject->getName(),
                );

            $body = 'return '.ValueType::generateToNativeCall(
                    $valueObject,
                    ($this->filterPropertyName)($valueObject->getName())
                ).';';

            $convertFromMethod = $phpParserFactory->method('convertFrom')
                ->makePublic()
                ->addParam($phpParserFactory->param(
                    ($this->filterPropertyName)($valueObject->getName()))->setType($valueObject->getName())->getNode()
                )
                ->setReturnType(ValueType::asTypeHint($valueObject))
                ->addStmts((new BodyGenerator($ctx->parser, $body))->generate())->getNode();
            $convertFromMethod->attrGroups = [new AttributeGroup([new Attribute(new Name('Converter'))])];
            $converter->addNodeVisitor(new PhpParserClassMethod($convertFromMethod));

            $convertToMethod = $phpParserFactory->method('convertTo')
                ->makePublic()
                ->addParam($phpParserFactory->param(
                    ($this->filterPropertyName)($valueObject->getName()))->setType(ValueType::asTypeHint($valueObject))
                )
                ->setReturnType($valueObject->getName())
                ->addStmts(
                    (new BodyGenerator(
                        $ctx->parser,
                        'return '.ValueType::generateFromNativeCall(
                            $valueObject,
                            ($this->filterPropertyName)($valueObject->getName())
                        ).';')
                    )->generate()
                )->getNode();
            $convertToMethod->attrGroups = [new AttributeGroup([new Attribute(new Name('Converter'))])];
            $converter->addNodeVisitor(new PhpParserClassMethod($convertToMethod));

            $converters[] = $converter;
        }

        foreach ($converters as $converter) {
            $fileCollection->add($converter);
        }
    }

    /**
     * @param string $voDirectory
     * @param string $voName
     * @param TypeSet $jsonSchema
     * @param Context $ctx
     * @return FileCollection
     */
    public function generateObject(string $voDirectory, string $voName, TypeSet $jsonSchema, Context $ctx): FileCollection
    {
        $voName = ($this->filterClassName)($voName);
        $sharedValueObjectsClassInfo = $this->classInfoList->classInfoForPath($voDirectory);

        $classBuilder = ClassBuilder::fromScratch(
            $voName,
            $this->extractNamespace($sharedValueObjectsClassInfo->getClassNamespaceFromPath($voDirectory), $jsonSchema->first())
        )->setFinal(true);

        $fileCollection = FileCollection::emptyList();

        $this->valueObjectFactory->generateClasses(
            $classBuilder,
            $fileCollection,
            $jsonSchema,
            $ctx->sharedValueObjectFolder
        );
        $this->addConstants($fileCollection);
        $this->addObjectFromValues($fileCollection, $ctx);
        $this->addGetterMethods($fileCollection);
        $this->addConverters($fileCollection, $ctx);

        return $fileCollection;
    }

    private function addObjectFromValues(FileCollection $valueObjects, Context $ctx): void
    {
        /** @var ClassBuilder $valueObject */
        foreach ($valueObjects as $valueObject) {
            if(ValueType::isValueObject($valueObject)) {
                // We are only interested in Assoc types that don't have a fromValues method yet
                continue;
            }

            $fromValues = ClassMethodBuilder::fromScratch('fromValues')->setStatic(true);
            $fromValues->setParameters(
                ...array_values(array_map(
                    static fn (ClassPropertyBuilder $property) => ParameterBuilder::fromScratch($property->getName(), $property->getType()),
                    $valueObject->getProperties()
                ))
            );

            $body = '$self = new self(); ';

            foreach ($valueObject->getProperties() as $property) {
                $body.= "\$self->{$property->getName()} = \${$property->getName()}; ";
            }

            $body.= 'return $self;';

            $fromValues->setBody($body);
            $fromValues->setReturnType('self');

            $valueObject->addMethod($fromValues);

            $constructor = ClassMethodBuilder::fromScratch('__construct')
                ->setPrivate();

            $valueObject->addMethod($constructor);
        }
    }

    private function extractSubNamespaceFromSharedValueObject(ClassBuilder $valueObject, Context $ctx): string
    {
        $valueObjectClassInfo = $this->classInfoList->classInfoForPath($ctx->sharedValueObjectFolder);

        $sharedNamespace = $valueObjectClassInfo->getClassNamespaceFromPath($ctx->sharedValueObjectFolder);

        return str_replace($sharedNamespace, '', $valueObject->getNamespace());
    }

    private function extractNamespace(string $classNamespacePath, TypeDefinition $typeDefinition): string
    {
        if (!$typeDefinition instanceof CustomSupport) {
            return $classNamespacePath;
        }
        $namespace = $typeDefinition->custom()['namespace'] ?? '';

        if ($namespace === '') {
            $namespace = $typeDefinition->custom()['ns'] ?? '';
        }

        return trim($classNamespacePath . '\\' . $namespace, '\\');
    }
}
