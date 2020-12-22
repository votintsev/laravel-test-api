<?php

namespace Votintsev\TestApi;


trait CRUD
{
    protected $crudConfig;

    abstract protected function createCRUDConfig() : CRUDConfig;

    protected function config()
    {
        if (! $this->crudConfig) {
            $this->crudConfig = $this->createCRUDConfig();
        }

        return $this->crudConfig;
    }

    protected function actingAsUser($user = false)
    {
//        Passport::actingAs($user ?: $this->config()->getActingUser(), ['*']);
        return $this;
    }

    public function list(CRUDOptions $options = null, callback $dataForCheckModify = null, $checkCount = false)
    {
        $options = $options ?: new CRUDOptions();
        $items = $options->listItems();
        $dataForCheck = $options->listDataForCheck($items);

        return $this->actingAsUser()
            ->json('GET', $options->urlPrefix ? $options->urlPrefix : $this->config()->getUrlPrefix())
            ->assertSuccessful()
            ->assertJsonCount($options->itemsCount(), 'data')
            ->assertJsonFragment($dataForCheck);
    }

    public function show(CRUDOptions $options = null)
    {
        $options = $options ?: new CRUDOptions();
        $item = factory($this->config()->getModelClass())->create($options->itemPreSet());
        // TODO fix, when we have protected field. May be without wrap $dataForCheck
        $dataForCheck = $item->toArray();
        if ($this->config()->hashid) $dataForCheck = $item->hideIdsForData($dataForCheck);
        $dataForCheck = $options->dataForCheckModify($item, $dataForCheck);

        $response = $this->actingAsUser()
            ->json('GET', $this->config()->urlToId($item))
            ->assertSuccessful()
            ->assertJsonFragment($dataForCheck)
        ;

        return $response;
    }

    public function create(array $data, array $dataCheck = null, CRUDOptions $options = null)
    {
        $options = $options ?: new CRUDOptions();

        $response = $this->actingAsUser()
            ->json('POST', $options->urlPrefix ? $options->urlPrefix : $this->config()->getUrlPrefix(), $data)
            ->assertSuccessful()
            ->assertJsonFragment($dataCheck ?: $data )
        ;

        return $response;
    }

    public function createFailValidation(array $data, array $fieldsHasError, CRUDOptions $options = null)
    {
        $options = $options ?: new CRUDOptions();

        $response = $this->actingAsUser()
            ->json('POST', $options->urlPrefix ? $options->urlPrefix : $this->config()->getUrlPrefix(), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors($fieldsHasError);

        return $response;
    }

    public function createFailExist($field)
    {
        $item = factory($this->config()->getModelClass())->create();
        $data = factory($this->config()->getModelClass())->make([$field => $item->{$field}]);

        $fieldsHasError = [$field];
        $this->createFailValidation($data->toArray(), $fieldsHasError);
    }

    /**
     * TODO allow set item via options config
     * @param $updateData
     * @param CRUDOptions|null $options
     * @param callable|null $itemModifyCallback
     * @return mixed
     */
    public function update($updateData, CRUDOptions $options = null, callable $itemModifyCallback = null)
    {
        $options = $options ?: new CRUDOptions();
        $item = factory($this->config()->getModelClass())->create($options->itemPreSet());

        if ($itemModifyCallback) {
            $item = $itemModifyCallback($item);
        }

        $response = $this->actingAsUser()
            ->json('PATCH', $this->config()->urlToId($item), $updateData)
            ->assertSuccessful();

        $checkData = $options->dataForAssertJsonFragment($updateData);
        foreach ($checkData as $data) {
            $response->assertJsonFragment($data);
        }

        return $response;
    }

    public function updateFailValidation(array $updateData, array $fieldsHasError, CRUDOptions $options = null)
    {
        $options = $options ?: new CRUDOptions();
        $item = factory($this->config()->getModelClass())->create($options->itemPreSet());

        $response = $this->actingAsUser()
            ->json('PATCH', $this->config()->urlToId($item), $updateData)
            ->assertStatus(422)
            ->assertJsonValidationErrors($fieldsHasError)
        ;

        return $response;
    }

    public function softDelete(CRUDOptions $options = null)
    {
        $options = $options ?: new CRUDOptions();
        $item = factory($this->config()->getModelClass())->create($options->itemPreSet());
        // TODO remove or fix JSON data
        $this->assertDatabaseHas($item->getTable(), $this->removeJsonFieldFromModel($item->toArray()));

        $response = $this->actingAsUser()
            ->json('DELETE', $this->config()->urlToId($item))
            ->assertSuccessful()
        ;

        $check = $item->toArray();
        unset($check['updated_at']);
        $this->assertDatabaseHas($item->getTable(), $this->removeJsonFieldFromModel($check));
        $this->assertSoftDeleted($item->getTable(), $this->removeJsonFieldFromModel($check));
    }

    public function forceDelete(CRUDOptions $options = null)
    {
        $options = $options ?: new CRUDOptions();
        $item = factory($this->config()->getModelClass())->create($options->itemPreSet());

        $this->assertDatabaseHas($item->getTable(), $this->removeJsonFieldFromModel($item->toArray()));

        $response = $this->actingAsUser()
            ->json('DELETE', $this->config()->urlToId($item))
            ->assertSuccessful()
        ;

        $this->assertDatabaseMissing($item->getTable(), ['id' => $item->id]);
    }

    protected function removeJsonFieldFromModel($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Generate a raw DB query to search for a JSON field.
     * Example:
     * $this->assertDatabaseHas('table_name', ['json_field' => CRUD::castToJson([$user->currentPublisher->email])]);
     *
     * @param  array|json $json
     *
     * @return \Illuminate\Database\Query\Expression
     * @throws \Exception
     */
    public static function castToJson($json)
    {
        // Convert from array to json and add slashes, if necessary.
        if (is_array($json)) {
            $json = addslashes(json_encode($json));
        }
        // Or check if the value is malformed.
        elseif (is_null($json) || is_null(json_decode($json))) {
            throw new \Exception('A valid JSON string was not provided.');
        }
        return \DB::raw("CAST('{$json}' AS JSON)");
    }

    // TODO destroy
}
