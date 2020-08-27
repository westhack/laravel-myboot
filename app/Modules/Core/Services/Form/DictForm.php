<?php

namespace App\Modules\Core\Services\Form;

use App\Modules\Core\Models\Config;
use App\Modules\Core\Models\Dict;
use Symfony\Component\Yaml\Yaml;

class DictForm
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @param Dict $model
     */
    public function __construct(Dict $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $input
     *
     * @return Config|bool
     */
    public function save(array $input)
    {
        return $this->model->create($input);
    }

    /**
     * @param array $input
     *
     * @return bool|Config
     */
    public function update(array $input)
    {
        $id = array_get($input, 'id');

        $model = $this->find($id);

        return $model->fill($input)->save() ? $model : false;
    }

    /**
     * @param $id
     *
     * @return Config
     */
    public function find($id)
    {
        return $this->model->whereId($id)->firstOrFail();
    }
}
