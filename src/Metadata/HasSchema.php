<?php

declare(strict_types=1);

namespace Ecotone\InspectioCody\Metadata;

interface HasSchema
{
    public function schema(): array;
}
