<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Config;

use EventEngine\InspectioGraph\EventSourcingAnalyzer;
use EventEngine\InspectioGraph\VertexType;

trait DetermineValueObjectPathTrait
{
    abstract function sharedValueObjectFolder(): string;

    public function determineValueObjectPath(VertexType $type, EventSourcingAnalyzer $analyzer): string
    {
        return $this->sharedValueObjectFolder();
    }
}
