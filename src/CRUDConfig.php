<?php

namespace Votintsev\TestApi;


class CRUDConfig
{
//    use UserFake;

    private $modelClass;
    private $urlPrefix;
    private $targetURL;
    private $modelPreSet = [];
    private $actingUser;
    public $hashid = false;

    public function __construct($modelClass, $urlPrefix)
    {
        $this->modelClass = $modelClass;
        $this->urlPrefix = $urlPrefix;
    }

    /**
     * @return mixed
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @return mixed
     */
    public function getUrlPrefix()
    {
        return $this->urlPrefix;
    }

    public function setModelPreSet(array $array)
    {
        $this->modelPreSet = $array;
    }

    public function getModelPreSet()
    {
        return $this->modelPreSet;
    }

    public function getActingUser()
    {
        return $this->actingUser ? : $this->user();
    }

    public function setActingUser(User $user)
    {
        return $this->actingUser = $user;
    }

    public function urlToId($item)
    {
        return $this->getUrlPrefix() . '/' . ($this->hashid ? Hashids::connection(get_class($item))->encode($item->id) : $item->id);
    }

}
