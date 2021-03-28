<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Config;

use EventEngine\CodeGenerator\EventEngineAst\Config\BasePathTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\ClassInfoListTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterAggregateFolderTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterClassNameTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterConstNameTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterConstValueTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterDirectoryToNamespaceTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterMethodNameTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterNamespaceToDirectoryTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\FilterPropertyNameTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\PhpParserTrait;
use EventEngine\CodeGenerator\EventEngineAst\Config\PhpPrinterTrait;
use Ecotone\InspectioCody\Code\FileLoader;
use Ecotone\InspectioCody\Code\ValueObject\ValueObjectGenerator;
use Ecotone\InspectioCody\Context;
use OpenCodeModeling\CodeAst\FileCodeGenerator;
use OpenCodeModeling\Filter\FilterFactory;
use OpenCodeModeling\JsonSchemaToPhpAst\ValueObjectFactory;
use const DIRECTORY_SEPARATOR;

final class PreConfiguredCommand implements Command
{
    use BasePathTrait;
    use ClassInfoListTrait;
    use DeterminePathTrait;
    use DetermineValueObjectPathTrait;
    use FilterAggregateFolderTrait;
    use FilterClassNameTrait;
    use FilterConstNameTrait;
    use FilterConstValueTrait;
    use FilterPropertyNameTrait;
    use FilterMethodNameTrait;
    use FilterDirectoryToNamespaceTrait;
    use FilterNamespaceToDirectoryTrait;
    use PhpParserTrait;
    use PhpPrinterTrait;
    use SharedValueObjectFolderTrait;

    private ValueObjectGenerator $valueObjectGenerator;
    private ValueObjectFactory $valueObjectFactory;

    public function __construct()
    {
        $this->filterClassName = FilterFactory::classNameFilter();
        $this->filterConstName = FilterFactory::constantNameFilter();
        $this->filterConstValue = FilterFactory::constantValueFilter();
        $this->filterDirectoryToNamespace = FilterFactory::directoryToNamespaceFilter();
        $this->filterNamespaceToDirectory = FilterFactory::namespaceToDirectoryFilter();

        $this->setFilterAggregateFolder($this->filterClassName);
        $this->addComposerInfo($this->getBasePath() . DIRECTORY_SEPARATOR . 'composer.json');
    }

    public function getValueObjectGenerator(Context $ctx, FileLoader $fileLoader): ValueObjectGenerator
    {
        return new ValueObjectGenerator(
            $ctx->printer,
            $fileLoader,
            $this->getClassInfoList(),
            new ValueObjectFactory(
                $this->getClassInfoList(),
                $ctx->parser,
                $ctx->printer->getPrettyPrinter(),
                true,
                $this->getFilterClassName(),
                $this->getFilterPropertyName(),
                $this->getFilterMethodName(),
                $this->getFilterConstName(),
                $this->getFilterConstValue()
            ),
            new FileCodeGenerator($ctx->parser, $ctx->printer->getPrettyPrinter(), $this->getClassInfoList()),
            $this->getFilterPropertyName(),
            $this->getFilterClassName()
        );
    }
}
