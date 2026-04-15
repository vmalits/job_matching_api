<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\User\Enum\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class RoleJsonType extends JsonType
{
    /**
     * @return list<Role>|null
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        /** @var list<string>|null $values */
        $values = parent::convertToPHPValue($value, $platform);

        if (null === $values) {
            return null;
        }

        return array_map(static fn (string $role): Role => Role::from($role), $values);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        $strings = array_map(static fn (Role $role): string => $role->value, $value);

        return parent::convertToDatabaseValue($strings, $platform);
    }

    public function getName(): string
    {
        return 'role_json';
    }
}
