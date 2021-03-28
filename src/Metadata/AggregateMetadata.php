<?php

declare(strict_types=1);

namespace Ecotone\InspectioCody\Metadata;

use EventEngine\InspectioCody\Board\Exception\CodyError;
use EventEngine\InspectioGraph\Metadata\Metadata;
use OpenCodeModeling\JsonSchemaToPhp\Shorthand\Shorthand;

final class AggregateMetadata implements Metadata, HasSchema
{
    private string $identifier;

    /**
     * @var array<string, mixed>
     */
    private array $schema;

    private function __construct()
    {
    }

    public static function fromJsonMetadata(string $json): self
    {
        $self = new self();

        $data = MetadataFactory::decodeJson($json);

        $self->identifier = $data['identifier'] ?? 'id';

        if (!isset($data['schema'])) {
            throw CodyError::withError('Metadata is missing a schema object.');
        }

        $schema = $data['schema'];

        if ($data['shorthand'] ?? false) {
            unset($data['identifier'], $data['schema']);
            $customData = $data;

            $schema = Shorthand::convertToJsonSchema($schema, $customData);
        }

        $self->schema = $schema;

        return $self;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(): array
    {
        return $this->schema;
    }
}
