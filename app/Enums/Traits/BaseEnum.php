<?php

namespace App\Enums\Traits;

trait BaseEnum
{
    public static function fromName(self|string $name): ?self
    {
        foreach (self::cases() as $enum) {

            if ($enum->name == $name) {
                return $enum;
            }
        }

        return null;
    }

    public static function getLabelFromName(string $name): string
    {

        $enum = self::fromName($name);

        return $enum ? $enum->getLabel() : '';
    }

    public static function getNames(): array
    {
        $data = [];
        foreach (self::cases() as $enum) {
            if (! isset($data[$enum->name])) {
                $data[$enum->name] = $enum->name;
            }
        }

        return $data;
    }

    public static function getLabels()
    {
        foreach (self::cases() as $enum) {
            $data[] = [
                'label' => $enum->getLabel(),
                'value' => $enum->name,
            ];
        }

        return $data;
    }

    public function getLabel(): string
    {
        return trans('enumsLabel.'.$this->name);
    }
}
