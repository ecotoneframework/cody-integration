<?php

declare(strict_types=1);

namespace Ecotone\InspectioCody\Metadata;

use EventEngine\InspectioCody\Board\Exception\CodyError;
use EventEngine\InspectioGraph\Metadata\Metadata;
use OpenCodeModeling\JsonSchemaToPhp\Shorthand\Shorthand;
use function json_encode;

final class CommandMetadata implements Metadata, HasSchema
{
    /**
     * @var bool
     */
    private $newAggregate = false;

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

        $self->newAggregate = $data['newAggregate'] ?? false;

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

    public function newAggregate(): bool
    {
        return $this->newAggregate;
    }

    public function schema(): array
    {
        return $this->schema;
    }
}
