<?php

declare(strict_types=1);

namespace Ecotone\InspectioCody\Metadata;

use EventEngine\InspectioCody\Board\Exception\CodyError;
use EventEngine\InspectioGraph\Metadata\Metadata;
use OpenCodeModeling\JsonSchemaToPhp\Shorthand\Shorthand;

final class DocumentMetadata implements Metadata, HasSchema
{
    /**
     * @var array
     */
    private $query;

    /**
     * @var array
     */
    private $schema;

    private function __construct()
    {
    }

    public static function fromJsonMetadata(string $json, string $name): self
    {
        $self = new self();

        $data = MetadataFactory::decodeJson($json);

        if (!isset($data['schema'])) {
            throw CodyError::withError('Metadata is missing a schema object.');
        }

        $self->query = $data['query'] ?? [];

        $schema = $data['schema'];

        if ($data['shorthand'] ?? false) {
            unset($data['identifier'], $data['schema'], $data['query']);
            $customData = $data;

            $schema = Shorthand::convertToJsonSchema($schema, $customData);
        }

        if (!isset($schema['name'])) {
            $schema['name'] = $name;
        }

        $self->schema = $schema;

        return $self;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function schema(): array
    {
        return $this->schema;
    }
}
