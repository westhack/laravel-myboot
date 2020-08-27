<?php

namespace App\Modules\Core\Services\Form;

use App\Modules\Core\Models\Config;
use Symfony\Component\Yaml\Yaml;

class ConfigForm
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @param Config $model
     */
    public function __construct(Config $model)
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
        if (array_has($input, 'data')) {
            $input['data'] = $this->parseData($input['data']);
        }

        if (array_has($input, 'rules')) {
            $input['rules'] = $this->parseData($input['rules']);
        }

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

        if (array_has($input, 'data')) {
            $input['data'] = $this->parseData($input['data']);
        }

        if (array_has($input, 'rules')) {
            $input['rules'] = $this->parseData($input['rules']);
        }

        $model = $this->find($id);

        return $model->fill($input)->save() ? $model : false;
    }

    public function parseData($data)
    {
        if ($data == '') {
            return '';
        }

        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if ($data != '') {
            $data = Yaml::parse($data);
            if (is_array($data)) {
                return json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }

        return $data;
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
