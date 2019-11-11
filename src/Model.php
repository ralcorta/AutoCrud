<?php

namespace Pyxeel\AutoCrud;


class Model
{

    /**
     * Properties
     */
    private $name,
        $attributes,
        $relationships,
        $executed;

    /**
     * Create a new configurationModel.
     *
     * @return void
     */
    public function __construct($model, $config)
    {
        $this->name = $model;
        $this->attributes = $config['attributes'] ?? [];
        $this->relationships = $config['relationships'] ?? [];
        $this->executed = isset($config['executed']) && $config['executed'];
    }

    public function executed(): bool
    {
        return $this->executed;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }

    public function getAttributes(): array
    {
        return array_keys($this->attributes);
    }

    public function getAttributeString(): string
    {
        return $this->attributesToString();
    }

    private function attributesToString(): string
    {
        $keys = $this->getAttributes();

        return "'" . implode("',\n\t\t'", $keys) . "'";
    }


    public function getRules()
    {
        $rules = [];

        foreach ($this->attributes as $name => $properies)
            $rules[$name] = !is_array($properies['rules']) ? $properies['rules'] : explode("|", $properies['rules']);

        return $rules;
    }

    public function getRulesString()
    {
        return $this->rulesToString();
    }

    private function rulesToString(): string
    {
        $concat = '';

        foreach ($this->getRules() as $name => $rules)
            $concat .= "'" . $name . "' => '" . $rules . "',\n\t\t";

        return $concat;
    }

    public function getMigrations()
    {
        $concat = '';

        foreach ($this->attributes as $name => $config) {
            if (!isset($config['type']))
                continue;

            $concat .= '$table->' . $config['type'] . '("' . $name . '")';

            $concat .= isset($config['required']) && !$config['required'] ? '->nullable()' : '';

            $concat .= isset($config['default']) ? ("->default(" . ($this->isStringType($config['default']) ? "'" . $config['default'] . "'" : $config['default']) . ")") : '';

            $concat .= ";\n";
        }

        return $concat;
    }

    private function isStringType($type)
    {
        $numerics = [
            'bigIncrements',
            'bigInteger',
            'decimal',
            'double',
            'float',
            'increments',
            'integer',
            'mediumInteger',
            'smallInteger',
            'tinyInteger',
            'unsigned'
        ];

        return !in_array($type, $numerics);
    }
}
