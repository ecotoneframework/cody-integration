<?php

declare(strict_types=1);

namespace Ecotone\InspectioCody\Metadata;

use EventEngine\InspectioGraphCody\Node;
use EventEngine\InspectioGraph\Exception\RuntimeException;
use EventEngine\InspectioGraph\Metadata\Metadata;
use EventEngine\InspectioGraph\VertexType;

final class MetadataFactory
{
    public function __invoke(Node $vertex): Metadata
    {
        $metadata = (string)$vertex->metadata();

        switch ($vertex->type()) {
            case VertexType::TYPE_COMMAND:
                return CommandMetadata::fromJsonMetadata($metadata ?: '{}');
            case VertexType::TYPE_AGGREGATE:
                return AggregateMetadata::fromJsonMetadata($metadata ?: '{}');
            case VertexType::TYPE_EVENT:
                return EventMetadata::fromJsonMetadata($metadata ?: '{}');
            case VertexType::TYPE_DOCUMENT:
                return DocumentMetadata::fromJsonMetadata($metadata ?: '{}', $vertex->name());
            default:
                throw new RuntimeException(\sprintf('Given type "%s" is not supported', $vertex->type()));
        }
    }

    public static function decodeJson(string $json): array
    {
        return \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);
    }

    public static function encodeJson(array $json): string
    {
        $flags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT;

        return \json_encode($json, $flags);
    }
}
