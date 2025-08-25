<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentWorksheet implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->leftJoin("so_det as so_det_scope", "so_det_scope.id", "=", "output_defects.so_det_id")->leftJoin("so as so_scope", "so_scope.id", "=", "so_det_scope.id_so")->leftJoin("act_costing as act_costing_scope", "act_costing_scope.id", "=", "so_scope.id_cost")->where("act_costing_scope.kpno", "SMT/0725/053");
    }
}
