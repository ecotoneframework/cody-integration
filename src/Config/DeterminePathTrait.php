<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Config;

use EventEngine\CodeGenerator\EventEngineAst\Exception\RuntimeException;
use EventEngine\InspectioGraph\CommandType;
use EventEngine\InspectioGraph\EventSourcingAnalyzer;
use EventEngine\InspectioGraph\EventType;
use EventEngine\InspectioGraph\VertexType;
use const DIRECTORY_SEPARATOR;

trait DeterminePathTrait
{
    abstract function getFilterAggregateFolder(): ?callable;
    abstract function getBasePath();

    public function determinePath(VertexType $type, EventSourcingAnalyzer $analyzer): string
    {
        $filterAggregateFolder = $this->getFilterAggregateFolder();

        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Domain';

        switch (true) {
            case $type instanceof CommandType:
                $aggregate = $analyzer->aggregateMap()->aggregateByCommand($type);

                if ($filterAggregateFolder === null || $aggregate === null) {
                    return $path . DIRECTORY_SEPARATOR . 'Command';
                }

                $path .= DIRECTORY_SEPARATOR . ($filterAggregateFolder)($aggregate->label()) . DIRECTORY_SEPARATOR . 'Command';
                break;
            case $type instanceof EventType:
                if ($filterAggregateFolder === null) {
                    return $path . DIRECTORY_SEPARATOR . 'Event';
                }
                $aggregate = $analyzer->aggregateMap()->aggregateByEvent($type);

                if ($aggregate === null) {
                    throw new RuntimeException(
                        \sprintf('Event "%s" has no aggregate connection. Can not use aggregate name for path.',
                            $type->label())
                    );
                }
                $path .= DIRECTORY_SEPARATOR . ($filterAggregateFolder)($aggregate->label()) . DIRECTORY_SEPARATOR . 'Event';
                break;
            default:
                break;
        }

        return $path;
    }
}
