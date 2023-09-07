<?php

declare(strict_types=1);

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

class EloquentRepository implements RepositoryInterface
{
    public const PER_PAGE = 10;

    /**
     * @var
     */
    protected $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id)
    {
        $model = $this->findOrFail($id);
        $model->update($data);

        return $model;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param $id
     * @param mixed $columns
     * @return mixed
     */
    public function firstOrFail($columns)
    {
        return $this->model->firstOrFail($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->firstOrFail($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * @param array $attribute
     * @param array $columns
     * @param mixed $attributes
     * @return mixed
     */
    public function findFor($attributes, $columns = ['*'])
    {
        $model = $this->model->select('*');

        foreach ($attributes as $attribute => $value) {
            $model->where($attribute, '=', $value);
        }

        return $model->first($columns);
    }
}
