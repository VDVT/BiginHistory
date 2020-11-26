<?php
namespace Bigin\History\Supports;

trait HistoryValueAttributeTrait
{
    /**
     * [getOriginalMutator description]
     * @param  mixed $attr
     * @return mixed
     */
    protected function getOriginalMutator($attr)
    {
        $origin = $this->getOriginal($attr);

        return ($this->hasGetMutator($attr))
        ? $this->mutateAttribute($attr, $origin)
        : $origin;
    }

    /**
     * [getNewValueMutator description]
     * @param  mixed $attr
     * @param  mixed $newValue
     * @return mixed
     */
    protected function getNewValueMutator($attr, $newValue)
    {
        return ($this->hasGetMutator($attr))
        ? $this->mutateAttribute($attr, $newValue)
        : $newValue;
    }

    /**
     * [getHistoryDisplayAttribute description]
     * @param  mixed $attr
     * @return mixed
     */
    protected function getHistoryDisplayAttribute($attr)
    {
        return array_get($this->displayAttributes, $attr) ?? ucwords(implode(" ", explode("_", $attr)));
    }

    /**
     * [getHistoryDisplayTable description]
     * @return mixed
     */
    protected function getHistoryDisplayTable()
    {
        $tableName = $this->getTable();
        $displayTable = config("bigin.history.nameTables.{$tableName}");
        return $displayTable ?: $tableName;
    }

    /**
     * [getTargetHistory description]
     * @return mixed
     */
    protected function getTargetHistory(): array
    {
        $logTargetAttributes = property_exists($this, 'logTargetAttributes') ? $this->logTargetAttributes : [];

        return [
            'target_type' => $logTargetAttributes['target'] ?? null,
            'target_id' => $this->getAttribute($logTargetAttributes['primary'] ?? 'id'),
        ];
    }
}
