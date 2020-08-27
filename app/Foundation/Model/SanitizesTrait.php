<?php

namespace App\Foundation\Model;

trait SanitizesTrait
{
    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->sanitizer($this->attributes);
    }

    public function sanitizer($data)
    {
        if (!empty($this->filters())) {
            $this->addCustomFilters();
            $this->sanitizer = app('sanitizer')->make($data, $this->filters());

            return $this->sanitizer->sanitize();
        }

        return $data;
    }

    /**
     *  Add custom fields to the Sanitizer
     *
     *  @return void
     */
    public function addCustomFilters()
    {
        foreach ($this->customFilters() as $name => $filter) {
            app('sanitizer')->extend($name, $filter);
        }
    }

    /**
     *  Filters to be applied to the input.
     *
     *  @return void
     */
    public function filters()
    {
        return [];
    }

    /**
     *  Custom Filters to be applied to the input.
     *
     *  @return void
     */
    public function customFilters()
    {
        return [];
    }
}
