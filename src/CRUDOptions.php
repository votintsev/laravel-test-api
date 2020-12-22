<?php

namespace Votintsev\TestApi;


class CRUDOptions
{
    private $itemCount = 4;
    private $itemPreSet = [];
    private $dataForAssertJsonFragment = [];
    public $urlPrefix;
    public $dataForCheckModifyCallback;

    public static function create()
    {
        return new self();
    }

    public function itemsCount()
    {
        return $this->itemCount;
    }

    public function setItemsCount($value)
    {
        $this->itemCount = $value;
    }

    public function itemPreSet($value = false)
    {
        if ($value === false) {
            return $this->itemPreSet;
        } else {
            $this->itemPreSet = $value;
        }

        return $this;
    }

    public function dataForCheckModify($item, $data)
    {
        $f =  $this->dataForCheckModifyCallback;
        return $f ? $f($item, $data) : $data;
    }

    public function dataForAssertJsonFragment($data) : array
    {
        return $this->dataForAssertJsonFragment ?: $data;
    }

    public function setDataForAssertJsonFragment($data)
    {
        $this->dataForAssertJsonFragment[] = $data;
        return $this;
    }

    public function listItems()
    {
        return factory($this->config()->getModelClass(), $this->itemsCount())->create($this->itemPreSet());
    }

    public function listDataForCheck($items)
    {
        // TODO fix, when we have protected field. May be without wrap $dataForCheck
        $randomItem = $items->random();
        $dataForCheck = $randomItem->toArray();

        if ($this->config()->hashid) $dataForCheck = $randomItem->hideIdsForData($dataForCheck);
        $dataForCheck = $this->dataForCheckModify($randomItem, $dataForCheck);
    }
}
