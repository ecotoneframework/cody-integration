<?php

declare(strict_types=1);

namespace Ecotone\InspectioCody\Metadata;

use EventEngine\InspectioCody\Board\Exception\CodyError;
use EventEngine\InspectioGraph\Metadata\Metadata;
use OpenCodeModeling\JsonSchemaToPhp\Shorthand\Shorthand;

final class EventMetadata implements Metadata, HasSchema
{
    /**
     * @var bool
     */
    private $public = false;

    /**
     * @var array
     */
    private $schema;

    private function __construct()
    {
    }

    public static function fromJsonMetadata(string $json): self
    {
        $self = new self();

        $data = MetadataFactory::decodeJson($json);

        if (!isset($data['schema'])) {
            throw CodyError::withError('Metadata is missing a schema object.');
        }

        $self->public = $data['public'] ?? false;

        $schema = $data['schema'];

        if ($data['shorthand'] ?? false) {
            unset($data['identifier'], $data['schema'], $data['public']);
            $customData = $data;

            $schema = Shorthand::convertToJsonSchema($schema, $customData);
        }

        $self->schema = $schema;

        return $self;
    }

    public function public(): bool
    {
        return $this->public;
    }

    public function schema(): array
    {
        return $this->schema;
    }
}
