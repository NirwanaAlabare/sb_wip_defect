<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentWorksheetPacking implements Scope
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
        // $builder->leftJoin("so_det as so_det_scope", "so_det_scope.id", "=", "output_defects_packing.so_det_id")->leftJoin("so as so_scope", "so_scope.id", "=", "so_det_scope.id_so")->leftJoin("act_costing as act_costing_scope", "act_costing_scope.id", "=", "so_scope.id_cost")->whereIn("act_costing_scope.kpno", ["VGD/0625/004", "SGT/0725/088", "SGT/0425/043","SGT/0825/121","SGT/0825/122","SGT/0725/073", "SGT/0725/089", "SGT/0725/086", "SGT/0825/128", "VGD/0625/001", "VGD/0625/002", "VGD/0625/003", "VGD/0625/004", "SGT/0825/119", "SGT/0725/097", "SGT/0725/107", "SGT/0825/117", "SGT/0725/069", "SGT/0725/091", "SGT/0825/134"]);
    }
}
