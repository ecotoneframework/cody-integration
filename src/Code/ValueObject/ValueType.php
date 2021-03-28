<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code\ValueObject;

use OpenCodeModeling\CodeAst\Builder\ClassBuilder;
use function lcfirst;
use function ucfirst;

final class ValueType
{
    public const STRING = 'string';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const DATE_TIME = '\DateTime';
    public const BOOL = 'bool';
    public const COLLECTION = 'collection';
    public const ASSOC = 'assoc';

    public static function asTypeHint(ClassBuilder $valueObject): string
    {
        $nativeType = self::analyze($valueObject);

        switch ($nativeType) {
            case self::ASSOC:
            case self::COLLECTION:
                return 'array';
            default:
                return $nativeType;
        }
    }

    public static function generateFromNativeCall(ClassBuilder $valueObject, string $argName = null): string
    {
        if(null === $argName) {
            $argName = lcfirst($valueObject->getName());
        }

        $nativeType = self::analyze($valueObject);
        $type = ucfirst($nativeType);

        switch ($nativeType) {
            case self::ASSOC:
                $type = 'Values';
                break;
            case self::COLLECTION:
                $type = 'Items';
                break;
        }

        return $valueObject->getName()."::from{$type}(\${$argName})";
    }

    public static function generateToNativeCall(ClassBuilder $valueObject, string $varName = null): string
    {
        if(null === $varName) {
            $varName = lcfirst($valueObject->getName());
        }

        $nativeType = self::analyze($valueObject);
        $type = ucfirst($nativeType);

        switch ($nativeType) {
            case self::ASSOC:
            case self::COLLECTION:
                $type = 'Array';
                break;
        }

        return "\${$varName}->to{$type}()";
    }

    public static function isValueObject(ClassBuilder $valueObject): bool
    {
        return $valueObject->hasMethod('fromItems')
            || $valueObject->hasMethod('fromValues')
            || $valueObject->hasMethod('fromString')
            || $valueObject->hasMethod('fromInt')
            || $valueObject->hasMethod('fromFloat')
            || $valueObject->hasMethod('fromBool');
    }

    public static function isBuiltIn(string $type): bool
    {
        switch ($type) {
            case 'string':
            case 'int':
            case 'float':
            case 'bool':
            case 'object':
            case 'array':
            case 'iterable':
            case 'self':
            case 'static':
            case 'stdClass':
                return true;
        }

        return false;
    }

    public static function analyze(ClassBuilder $valueObject): string
    {
        switch (true) {
            case $valueObject->hasMethod('fromItems'):
                return self::COLLECTION;
            case $valueObject->hasMethod('fromValues'):
                return self::ASSOC;
            case $valueObject->hasMethod('fromInt'):
                return self::INT;
            case $valueObject->hasMethod('fromFloat'):
                return self::FLOAT;
            case $valueObject->hasMethod('fromBool'):
                return self::BOOL;
            case $valueObject->hasMethod('fromString'):
                return self::STRING;
            default:
                throw new \InvalidArgumentException(
                    'Given class builder is not a value object. Cannot detect native type. Got ClassBuilder for '
                    . $valueObject->getName()
                );
        }
    }
}
