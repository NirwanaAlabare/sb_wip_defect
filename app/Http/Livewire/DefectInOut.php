<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\UserPassword;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\DefectInOut as DefectInOutModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;
use DB;

class DefectInOut extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $date;

    public $lines;
    public $orders;

    public $defectInDate;
    public $defectInLine;
    public $defectInOutputType;
    public $defectInQty;

    public $defectInDateModal;
    public $defectInOutputModal;
    public $defectInLineModal;
    public $defectInMasterPlanModal;
    public $defectInSizeModal;
    public $defectInTypeModal;
    public $defectInAreaModal;
    public $defectInQtyModal;

    public $defectOutDate;
    public $defectOutLine;
    public $defectOutOutputType;
    public $defectOutQty;

    public $defectOutDateModal;
    public $defectOutOutputModal;
    public $defectOutLineModal;
    public $defectOutMasterPlanModal;
    public $defectOutSizeModal;
    public $defectOutTypeModal;
    public $defectOutAreaModal;
    public $defectOutQtyModal;

    public $defectInMasterPlanOutput;
    public $defectOutMasterPlanOutput;

    public $defectInSelectedMasterPlan;
    public $defectInSelectedSize;
    public $defectInSelectedType;
    public $defectInSelectedArea;

    public $defectOutSelectedMasterPlan;
    public $defectOutSelectedSize;
    public $defectOutSelectedType;
    public $defectOutSelectedArea;

    public $defectInOutSearch;

    public $defectInOutOutputType;

    public $productTypeImage;
    public $defectPositionX;
    public $defectPositionY;

    public $mode;

    public $loadingMasterPlan;

    public $baseUrl;

    public $listeners = [
        'setDate' => 'setDate',
        'hideDefectAreaImageClear' => 'hideDefectAreaImage',
        'refreshComponent' => 'refreshComponent'
    ];

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function mount()
    {
        $this->date = date('Y-m-d');

        $this->mode = 'in';
        $this->lines = null;
        $this->orders = null;

        // Defect In init value
        $this->defectInShowPage = 10;
        $this->defectInOutputType = 'qc';
        $this->defectInDate = date('Y-m-d');
        $this->defectInLine = null;
        $this->defectInMasterPlan = null;
        $this->defectInSelectedMasterPlan = null;
        $this->defectInSelectedSize = null;
        $this->defectInSelectedType = null;
        $this->defectInSelectedArea = null;
        $this->defectInMasterPlanOutput = null;
        $this->defectInSelectedList = [];
        $this->defectInSearch = null;
        $this->defectInListAllChecked = null;

        // Defect Out init value
        $this->defectOutShowPage = 10;
        $this->defectOutOutputType = 'qc';
        $this->defectOutDate = date('Y-m-d');
        $this->defectOutLine = null;
        $this->defectOutMasterPlan = null;
        $this->defectOutSelectedMasterPlan = null;
        $this->defectOutSelectedSize = null;
        $this->defectOutSelectedType = null;
        $this->defectOutSelectedArea = null;
        $this->defectOutMasterPlanOutput = null;
        $this->defectOutSelectedList = [];
        $this->defectOutSearch = null;
        $this->defectOutListAllChecked = false;

        $this->defectInOutShowPage = 10;

        $this->productTypeImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;

        $this->loadingMasterPlan = false;
        $this->baseUrl = url('/');
    }

    public function changeMode($mode)
    {
        $this->mode = $mode;
    }

    public function updatingDefectInSearch()
    {
        $this->resetPage("defectInPage");
    }

    public function updatingDefectOutSearch()
    {
        $this->resetPage("defectOutPage");
    }

    public function updatingDefectInOutSearch()
    {
        $this->resetPage("defectInOutPage");
    }

    public function updatedPaginators($page, $pageName) {
        if ($this->defectInListAllChecked == true) {
            $this->selectAllDefectIn();
        }

        if ($this->defectOutListAllChecked == true) {
            $this->selectAllDefectOut();
        }
    }

    public function selectAllDefectIn()
    {
        if ($this->defectInOutputType == "packing") {
            $defectInQuery = DefectPacking::selectRaw("
                master_plan.id master_plan_id,
                output_defects_packing.defect_type_id,
                output_defects_packing.so_det_id,
                'packing' output_type
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects_packing.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects_packing.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects_packing.defect_type_id", $this->defectInSelectedType);
            }
            $defectInQuery->groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects_packing.so_det_id")->
                orderBy("master_plan.sewing_line")->
                orderBy("master_plan.id_ws")->
                orderBy("master_plan.color")->
                orderBy("output_defect_types.defect_type")->
                orderBy("output_defects_packing.so_det_id");
        } else {
            $defectInQuery = Defect::selectRaw("
                master_plan.id master_plan_id,
                output_defects.defect_type_id,
                output_defects.so_det_id,
                'qc' output_type
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects.defect_type_id", $this->defectInSelectedType);
            }
            $defectInQuery->groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id")->
                orderBy("master_plan.sewing_line")->
                orderBy("master_plan.id_ws")->
                orderBy("master_plan.color")->
                orderBy("output_defect_types.defect_type")->
                orderBy("output_defects.so_det_id");
        }

        $this->defectInSelectedList = collect($defectInQuery->
            get()->
            toArray()
        );

        $this->defectInListAllChecked = true;
    }

    public function selectAllDefectOut()
    {
        $defectOutQuery = DefectInOutModel::selectRaw("
            master_plan.id master_plan_id,
            output_defects.defect_type_id,
            output_defects.so_det_id,
            output_defect_in_out.output_type
        ")->
        leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        where("output_defect_in_out.status", "defect")->
        where("output_defect_in_out.type", Auth::user()->Groupp)->
        where("output_defect_in_out.output_type", $this->defectOutOutputType);
        if ($this->defectOutSearch) {
            $defectOutQuery->whereRaw("(
                master_plan.tgl_plan LIKE '%".$this->defectOutSearch."%' OR
                master_plan.sewing_line LIKE '%".$this->defectOutSearch."%' OR
                act_costing.kpno LIKE '%".$this->defectOutSearch."%' OR
                act_costing.styleno LIKE '%".$this->defectOutSearch."%' OR
                master_plan.color LIKE '%".$this->defectOutSearch."%' OR
                output_defect_types.defect_type LIKE '%".$this->defectOutSearch."%' OR
                so_det.size LIKE '%".$this->defectOutSearch."%'
            )");
        }
        // if ($this->defectOutDate) {
        //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$this->defectOutDate." 00:00:00", $this->defectOutDate." 23:59:59"]);
        // }
        if ($this->defectOutLine) {
            $defectOutQuery->where("master_plan.sewing_line", $this->defectOutLine);
        }
        if ($this->defectOutSelectedMasterPlan) {
            $defectOutQuery->where("master_plan.id", $this->defectOutSelectedMasterPlan);
        }
        if ($this->defectOutSelectedSize) {
            $defectOutQuery->where("output_defects.so_det_id", $this->defectOutSelectedSize);
        }
        if ($this->defectOutSelectedType) {
            $defectOutQuery->where("output_defects.defect_type_id", $this->defectOutSelectedType);
        }
        $this->defectOutSelectedList = collect($defectOutQuery->
            groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id")->
            orderBy("master_plan.sewing_line")->
            orderBy("master_plan.id_ws")->
            orderBy("master_plan.color")->
            orderBy("output_defect_types.defect_type")->
            orderBy("output_defects.so_det_id")->
            get()->
            toArray()
        );

        $this->defectOutListAllChecked = true;
    }

    public function unselectAllDefectIn()
    {
        $this->defectInSelectedList = [];

        $this->defectInListAllChecked = false;
    }

    public function unselectAllDefectOut()
    {
        $this->defectOutSelectedList = [];

        $this->defectOutListAllChecked = false;
    }

    public function addDefectInSelectedList($data) {
        $thisKey = ["master_plan_id", "defect_type_id", "so_det_id", "output_type"];
        $thisData = explode("-", $data);

        $thisArr = array_combine($thisKey, $thisData);

        if (is_array($this->defectInSelectedList)) {
            $this->defectInSelectedList = collect([$thisArr]);
        } else {
            $this->defectInSelectedList->push($thisArr);
        }
    }

    public function removeDefectInSelectedList($data) {
        $thisKey = ["master_plan_id", "defect_type_id", "so_det_id", "output_type"];
        $thisData = explode("-", $data);

        $thisArr = array_combine($thisKey, $thisData);

        if ($this->defectOutSelectedList) {
            $key = $this->defectInSelectedList->search(function($item) use($thisArr) {
                return $item['master_plan_id'] == $thisArr['master_plan_id'] && $item['defect_type_id'] == $thisArr['defect_type_id'] && $item['so_det_id'] == $thisArr['so_det_id'] && $item['output_type'] == $thisArr['output_type'];
            });

            $this->defectInSelectedList->pull($key);
        }
    }

    public function saveCheckedDefectIn() {
        $defectInArr = [];
        foreach ($this->defectInSelectedList as $defectIn) {
            if ($defectIn['output_type'] == 'packing') {
                $thisDefects = DefectPacking::selectRaw("
                    output_defects_packing.id as defect_id,
                    'defect' as status,
                    '".Auth::user()->Groupp."' as type,
                    'packing' as output_type,
                    '".Auth::user()->username."' as created_by,
                    '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as created_at,
                    '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as updated_at
                ")->
                where("defect_status", 'defect')->
                where("master_plan_id", $defectIn['master_plan_id'])->
                where("defect_type_id", $defectIn['defect_type_id'])->
                where("so_det_id", $defectIn['so_det_id'])->
                get()->
                toArray();
            } else {
                $thisDefects = Defect::selectRaw("
                    output_defects.id as defect_id,
                    'defect' as status,
                    '".Auth::user()->Groupp."' as type,
                    'qc' as output_type,
                    '".Auth::user()->username."' as created_by,
                    '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as created_at,
                    '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as updated_at
                ")->
                where("defect_status", 'defect')->
                where("master_plan_id", $defectIn['master_plan_id'])->
                where("defect_type_id", $defectIn['defect_type_id'])->
                where("so_det_id", $defectIn['so_det_id'])->
                get()->
                toArray();
            }

            $data = array_map(function ($value) {
                return (array)$value;
            }, $thisDefects);

            array_push($defectInArr, ...$data);
        }

        DefectInOutModel::insert($defectInArr);

        if (count($defectInArr) > 0) {
            $this->defectInSelectedList = [];

            $this->defectInListAllChecked = false;

            $this->emit('alert', 'success', count($defectInArr)." DEFECT berhasil di masuk '".Auth::user()->Groupp."'");
        } else {
            $this->emit('alert', 'warning', "DEFECT gagal masuk '".Auth::user()->Groupp."'");
        }
    }

    public function addDefectOutSelectedList($data) {
        $thisKey = ["master_plan_id", "defect_type_id", "so_det_id", "output_type"];
        $thisData = explode("-", $data);

        $thisArr = array_combine($thisKey, $thisData);

        if (is_array($this->defectOutSelectedList)) {
            $this->defectOutSelectedList = collect([$thisArr]);
        } else {
            $this->defectOutSelectedList->push($thisArr);
        }
    }

    public function removeDefectOutSelectedList($data) {
        $thisKey = ["master_plan_id", "defect_type_id", "so_det_id", "output_type"];
        $thisData = explode("-", $data);

        $thisArr = array_combine($thisKey, $thisData);

        if (!is_array($this->defectOutSelectedList)) {
            $key = $this->defectOutSelectedList->search(function($item) use($thisArr) {
                return $item['master_plan_id'] == $thisArr['master_plan_id'] && $item['defect_type_id'] == $thisArr['defect_type_id'] && $item['so_det_id'] == $thisArr['so_det_id'] && $item['output_type'] == $thisArr['output_type'];
            });
        }

        $this->defectOutSelectedList->pull($key);
    }

    public function saveCheckedDefectOut() {
        $defectInIds = [];
        foreach ($this->defectOutSelectedList as $defectOut) {
            $thisDefectIn = DefectInOutModel::selectRaw("
                    output_defect_in_out.id
                ")->
                leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
                where("output_defect_in_out.status", 'defect')->
                where("output_defect_in_out.output_type", $this->defectOutOutputType)->
                where("output_defect_in_out.type", Auth::user()->Groupp)->
                where("output_defect_in_out.created_by", Auth::user()->username)->
                where("output_defects.master_plan_id", $defectOut['master_plan_id'])->
                where("output_defects.defect_type_id", $defectOut['defect_type_id'])->
                where("output_defects.so_det_id", $defectOut['so_det_id'])->
                pluck("id")->
                toArray();

            array_push($defectInIds, ...$thisDefectIn);
        }

        DefectInOutModel::whereIn("id", $defectInIds)->update([
            "status" => "reworked",
            "updated_at" => Carbon::now(),
            "reworked_at" => Carbon::now()
        ]);

        if (count($defectInIds) > 0) {
            $this->defectOutSelectedList = [];

            $this->defectOutListAllChecked = false;

            $this->emit('alert', 'success', count($defectInIds)." DEFECT berhasil keluar dari '".Auth::user()->Groupp."'");
        } else {
            $this->emit('alert', 'warning', "DEFECT gagal keluar '".Auth::user()->Groupp."'");
        }
    }

    public function saveFilteredDefectIn() {
        if ($this->defectInOutputType == 'packing') {
            $defectInQuery = DefectPacking::selectRaw("
                output_defects_packing.id as defect_id,
                'defect' as status,
                '".Auth::user()->Groupp."' as type,
                'packing' as output_type,
                '".Auth::user()->username."' as created_by,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as created_at,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects_packing.defect_status", "defect")->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects_packing.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects_packing.defect_type_id", $this->defectInSelectedType);
            }
        } else {
            $defectInQuery = Defect::selectRaw("
                output_defects.id as defect_id,
                'defect' as status,
                '".Auth::user()->Groupp."' as type,
                'qc' as output_type,
                '".Auth::user()->username."' as created_by,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as created_at,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects.defect_status", "defect")->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects.defect_type_id", $this->defectInSelectedType);
            }
        }

        if ($this->defectInQty > 0) {
            $defectIn = $defectInQuery->
                orderBy("sewing_line")->
                orderBy("id_ws")->
                orderBy("color")->
                orderBy("defect_type")->
                orderBy("so_det_id")->
                orderBy("updated_at")->
                limit($this->defectInQty)->
                get()->
                toArray();

            DefectInOutModel::insert($defectIn);

            if (count($defectIn) > 0) {
                $this->emit('alert', 'success', count($defectIn)." DEFECT berhasil di masuk ke '".Auth::user()->Groupp."'");
            } else {
                $this->emit('alert', 'warning', "DEFECT gagal masuk ke '".Auth::user()->Groupp."'");
            }
        } else {
            $this->emit('alert', 'warning', "Qty DEFECT IN 0");
        }
    }

    public function saveAllDefectIn() {
        if ($this->defectInOutputType == 'packing') {
            $defectInQuery = Defect::selectRaw("
                output_defects.id as defect_id,
                'defect' as status,
                '".Auth::user()->Groupp."' as type,
                'packing' as output_type,
                '".Auth::user()->username."' as created_by,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as created_at,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects.defect_status", "defect")->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects.defect_type_id", $this->defectInSelectedType);
            }
        } else {
            $defectInQuery = Defect::selectRaw("
                output_defects.id as defect_id,
                'defect' as status,
                '".Auth::user()->Groupp."' as type,
                'qc' as output_type,
                '".Auth::user()->username."' as created_by,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as created_at,
                '".Carbon::now()->addHour(7)->format("Y-m-d H:i:s")."' as updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects.defect_status", "defect")->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects.defect_type_id", $this->defectInSelectedType);
            }
        }

        $defectIn = $defectInQuery->
            orderBy("sewing_line")->
            orderBy("id_ws")->
            orderBy("color")->
            orderBy("defect_type")->
            orderBy("so_det_id")->
            get()->
            toArray();

        DefectInOutModel::insert($defectIn);

        if (count($defectIn) > 0) {
            $this->emit('alert', 'success', count($defectIn)." DEFECT berhasil di masuk ke '".Auth::user()->Groupp."'");
        } else {
            $this->emit('alert', 'warning', "DEFECT gagal masuk ke '".Auth::user()->Groupp."'");
        }
    }

    public function saveFilteredDefectOut() {
        $defectOutQuery = DefectInOutModel::selectRaw("
            output_defect_in_out.id
        ")->
        leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        where("output_defect_in_out.status", "defect")->
        where("output_defect_in_out.output_type", $this->defectOutOutputType)->
        where("output_defect_in_out.type", Auth::user()->Groupp);
        if ($this->defectOutSearch) {
            $defectOutQuery->whereRaw("(
                master_plan.tgl_plan LIKE '%".$this->defectOutSearch."%' OR
                master_plan.sewing_line LIKE '%".$this->defectOutSearch."%' OR
                act_costing.kpno LIKE '%".$this->defectOutSearch."%' OR
                act_costing.styleno LIKE '%".$this->defectOutSearch."%' OR
                master_plan.color LIKE '%".$this->defectOutSearch."%' OR
                output_defect_types.defect_type LIKE '%".$this->defectOutSearch."%' OR
                so_det.size LIKE '%".$this->defectOutSearch."%'
            )");
        }
        // if ($this->defectOutDate) {
        //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$this->defectOutDate." 00:00:00", $this->defectOutDate." 23:59:59"]);
        // }
        if ($this->defectOutLine) {
            $defectInQuery->where("master_plan.sewing_line", $this->defectOutLine);
        }
        if ($this->defectOutSelectedMasterPlan) {
            $defectOutQuery->where("master_plan.id", $this->defectOutSelectedMasterPlan);
        }
        if ($this->defectOutSelectedSize) {
            $defectOutQuery->where("output_defects.so_det_id", $this->defectOutSelectedSize);
        }
        if ($this->defectOutSelectedType) {
            $defectOutQuery->where("output_defects.defect_type_id", $this->defectOutSelectedType);
        }

        if ($this->defectOutQty > 0) {
            $defectOutList = $defectOutQuery->
                orderBy("master_plan.sewing_line")->
                orderBy("master_plan.id_ws")->
                orderBy("master_plan.color")->
                orderBy("output_defect_types.defect_type")->
                orderBy("output_defects.so_det_id")->
                limit($this->defectOutQty)->
                pluck("id");

            DefectInOutModel::whereIn("id", $defectOutList)->update([
                "status" => "reworked",
                "updated_at" => Carbon::now(),
                "reworked_at" => Carbon::now()
            ]);

            if (count($defectOutList) > 0) {
                $this->emit('alert', 'success', count($defectOutList)." DEFECT berhasil keluar dari '".Auth::user()->Groupp."'");
            } else {
                $this->emit('alert', 'warning', "DEFECT gagal keluar dari '".Auth::user()->Groupp."'");
            }
        } else {
            $this->emit('alert', 'warning', "Qty DEFECT OUT 0");
        }
    }

    public function saveAllDefectOut() {
        $defectOutQuery = DefectInOutModel::selectRaw("
            output_defect_in_out.id
        ")->
        leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        where("output_defect_in_out.status", "defect")->
        where("output_defect_in_out.output_type", $this->defectOutOutputType)->
        where("output_defect_in_out.type", Auth::user()->Groupp);
        if ($this->defectOutSearch) {
            $defectOutQuery->whereRaw("(
                master_plan.tgl_plan LIKE '%".$this->defectOutSearch."%' OR
                master_plan.sewing_line LIKE '%".$this->defectOutSearch."%' OR
                act_costing.kpno LIKE '%".$this->defectOutSearch."%' OR
                act_costing.styleno LIKE '%".$this->defectOutSearch."%' OR
                master_plan.color LIKE '%".$this->defectOutSearch."%' OR
                output_defect_types.defect_type LIKE '%".$this->defectOutSearch."%' OR
                so_det.size LIKE '%".$this->defectOutSearch."%'
            )");
        }
        // if ($this->defectOutDate) {
        //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$this->defectOutDate." 00:00:00", $this->defectOutDate." 23:59:59"]);
        // }
        if ($this->defectOutLine) {
            $defectInQuery->where("master_plan.sewing_line", $this->defectOutLine);
        }
        if ($this->defectOutSelectedMasterPlan) {
            $defectOutQuery->where("master_plan.id", $this->defectOutSelectedMasterPlan);
        }
        if ($this->defectOutSelectedSize) {
            $defectOutQuery->where("output_defects.so_det_id", $this->defectOutSelectedSize);
        }
        if ($this->defectOutSelectedType) {
            $defectOutQuery->where("output_defects.defect_type_id", $this->defectOutSelectedType);
        }
        $defectOutList = $defectOutQuery->
            orderBy("master_plan.sewing_line")->
            orderBy("master_plan.id_ws")->
            orderBy("master_plan.color")->
            orderBy("output_defect_types.defect_type")->
            orderBy("output_defects.so_det_id")->
            pluck("id");

        DefectInOutModel::whereIn("id", $defectOutList)->update([
            "status" => "reworked",
            "updated_at" => Carbon::now(),
            "reworked_at" => Carbon::now()
        ]);

        if (count($defectOutList) > 0) {
            $this->emit('alert', 'success', count($defectOutList)." DEFECT berhasil keluar dari '".Auth::user()->Groupp."'");
        } else {
            $this->emit('alert', 'warning', "DEFECT gagal keluar dari '".Auth::user()->Groupp."'");
        }
    }

    public function preSaveSelectedDefectIn($data) {
        $thisData = explode("-", $data);

        if ($thisData[3] == "packing") {
            $defectIn = DefectPacking::selectRaw("
                master_plan.tgl_plan,
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects_packing.defect_type_id,
                output_defect_types.defect_type,
                output_defects_packing.so_det_id,
                so_det.size,
                COUNT(output_defects_packing.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_in_out", "output_defect_in_out.defect_id", "=", "output_defects_packing.id")->
            where("output_defects_packing.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            where("master_plan.id", $thisData[0])->
            where("output_defect_types.id", $thisData[1])->
            where("output_defects_packing.so_det_id", $thisData[2])->
            whereNull("output_defect_in_out.id")->
            groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects_packing.so_det_id")->
            orderBy("master_plan.sewing_line")->
            orderBy("master_plan.id_ws")->
            orderBy("master_plan.color")->
            orderBy("output_defect_types.defect_type")->
            orderBy("output_defects_packing.so_det_id")->
            first();
        } else {
            $defectIn = Defect::selectRaw("
                master_plan.tgl_plan,
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects.defect_type_id,
                output_defect_types.defect_type,
                output_defects.so_det_id,
                so_det.size,
                COUNT(output_defects.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", "output_defect_in_out.defect_id", "=", "output_defects.id")->
            where("output_defects.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            where("master_plan.id", $thisData[0])->
            where("output_defect_types.id", $thisData[1])->
            where("output_defects.so_det_id", $thisData[2])->
            whereNull("output_defect_in_out.id")->
            groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id")->
            orderBy("master_plan.sewing_line")->
            orderBy("master_plan.id_ws")->
            orderBy("master_plan.color")->
            orderBy("output_defect_types.defect_type")->
            orderBy("output_defects.so_det_id")->
            first();
        }

        $this->defectInOutputModal = $thisData[3];
        $this->defectInDateModal = $defectIn->tgl_plan;
        $this->defectInLineModal = $defectIn->sewing_line;
        $this->defectInMasterPlanTextModal = $defectIn->ws." - ".$defectIn->style." - ".$defectIn->color;
        $this->defectInMasterPlanModal = $defectIn->master_plan_id;
        $this->defectInSizeTextModal = $defectIn->size;
        $this->defectInSizeModal = $defectIn->so_det_id;
        $this->defectInTypeTextModal = $defectIn->defect_type;
        $this->defectInTypeModal = $defectIn->defect_type_id;
        $this->defectInQtyModal = $defectIn->defect_qty;

        $this->emit('showModal', 'defectIn');
    }

    public function saveSelectedDefectIn() {
        if ($this->defectInOutputModal == "packing") {
            $defectInQuery = DefectPacking::selectRaw("
                output_defects_packing.id as defect_id,
                'defect' as status,
                '".Auth::user()->Groupp."' as type,
                'packing' as output_type,
                '".Auth::user()->username."' as created_by,
                '".Carbon::now()->addHour(7)."' as created_at,
                '".Carbon::now()->addHour(7)."' as updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects_packing.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInDateModal) {
                $defectInQuery->where("master_plan.tgl_plan", $this->defectInDateModal);
            }
            if ($this->defectInLineModal) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLineModal);
            }
            if ($this->defectInMasterPlanModal) {
                $defectInQuery->where("master_plan.id", $this->defectInMasterPlanModal);
            }
            if ($this->defectInSizeModal) {
                $defectInQuery->where("output_defects_packing.so_det_id", $this->defectInSizeModal);
            }
            if ($this->defectInTypeModal) {
                $defectInQuery->where("output_defects_packing.defect_type_id", $this->defectInTypeModal);
            }
        } else {
            $defectInQuery = Defect::selectRaw("
                output_defects.id as defect_id,
                'defect' as status,
                '".Auth::user()->Groupp."' as type,
                'qc' as output_type,
                '".Auth::user()->username."' as created_by,
                '".Carbon::now()->addHour(7)."' as created_at,
                '".Carbon::now()->addHour(7)."' as updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            where("output_defects.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInDateModal) {
                $defectInQuery->where("master_plan.tgl_plan", $this->defectInDateModal);
            }
            if ($this->defectInLineModal) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLineModal);
            }
            if ($this->defectInMasterPlanModal) {
                $defectInQuery->where("master_plan.id", $this->defectInMasterPlanModal);
            }
            if ($this->defectInSizeModal) {
                $defectInQuery->where("output_defects.so_det_id", $this->defectInSizeModal);
            }
            if ($this->defectInTypeModal) {
                $defectInQuery->where("output_defects.defect_type_id", $this->defectInTypeModal);
            }
        }

        if ($this->defectInQtyModal > 0 && $this->defectInQtyModal <= $defectInQuery->count()) {
            $defectIn = $defectInQuery->
                orderBy("master_plan.sewing_line")->
                orderBy("master_plan.id_ws")->
                orderBy("master_plan.color")->
                orderBy("output_defect_types.defect_type")->
                limit($this->defectInQtyModal)->
                get()->
                toArray();

            DefectInOutModel::insert($defectIn);

            if (count($defectIn) > 0) {
                $this->emit('alert', 'success', count($defectIn)." DEFECT berhasil di masuk ke '".Auth::user()->Groupp."'");

                $this->emit('hideModal', 'defectIn');
            } else {
                $this->emit('alert', 'warning', "DEFECT gagal masuk ke '".Auth::user()->Groupp."'");
            }
        } else {
            $this->emit('alert', 'error', "Qty DEFECT IN tidak valid (<b>MIN:1</b> | <b>MAX:".$defectInQuery->count()."</b>)");
        }
    }

    public function preSaveSelectedDefectOut($data) {
        $thisData = explode("-", $data);

        $defectOut = DefectInOutModel::selectRaw("
            master_plan.tgl_plan,
            master_plan.id master_plan_id,
            master_plan.id_ws,
            master_plan.sewing_line,
            act_costing.kpno as ws,
            act_costing.styleno as style,
            master_plan.color as color,
            output_defects.defect_type_id,
            output_defect_types.defect_type,
            output_defects.so_det_id,
            so_det.size,
            COUNT(output_defects.id) defect_qty
        ")->
        leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        where("output_defect_types.allocation", Auth::user()->Groupp)->
        where("output_defect_in_out.type", Auth::user()->Groupp)->
        where("output_defect_in_out.output_type", $this->defectOutOutputType)->
        where("output_defect_in_out.created_by", Auth::user()->username)->
        where("master_plan.id", $thisData[0])->
        where("output_defect_types.id", $thisData[1])->
        where("output_defects.so_det_id", $thisData[2])->
        where("output_defect_in_out.output_type", $thisData[3])->
        groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id")->
        orderBy("master_plan.sewing_line")->
        orderBy("master_plan.id_ws")->
        orderBy("master_plan.color")->
        orderBy("output_defect_types.defect_type")->
        orderBy("output_defects.so_det_id")->
        first();

        $this->defectOutOutputModal = $thisData[3];
        $this->defectOutDateModal = $defectOut->tgl_plan;
        $this->defectOutLineModal = $defectOut->sewing_line;
        $this->defectOutMasterPlanTextModal = $defectOut->ws." - ".$defectOut->style." - ".$defectOut->color;
        $this->defectOutMasterPlanModal = $defectOut->master_plan_id;
        $this->defectOutSizeTextModal = $defectOut->size;
        $this->defectOutSizeModal = $defectOut->so_det_id;
        $this->defectOutTypeTextModal = $defectOut->defect_type;
        $this->defectOutTypeModal = $defectOut->defect_type_id;
        $this->defectOutQtyModal = $defectOut->defect_qty;

        $this->emit('showModal', 'defectOut');
    }

    public function saveSelectedDefectOut() {
        $defectOutQuery = DefectInOutModel::selectRaw("
            output_defect_in_out.id
        ")->
        leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        where("output_defect_types.allocation", Auth::user()->Groupp)->
        where("output_defect_in_out.status", "defect")->
        where("output_defect_in_out.output_type", $this->defectOutOutputType)->
        where("output_defect_in_out.type", Auth::user()->Groupp);
        if ($this->defectOutDateModal) {
            $defectOutQuery->where("master_plan.tgl_plan", $this->defectOutDateModal);
        }
        if ($this->defectOutLineModal) {
            $defectOutQuery->where("master_plan.sewing_line", $this->defectOutLineModal);
        }
        if ($this->defectOutMasterPlanModal) {
            $defectOutQuery->where("master_plan.id", $this->defectOutMasterPlanModal);
        }
        if ($this->defectOutSizeModal) {
            $defectOutQuery->where("output_defects.so_det_id", $this->defectOutSizeModal);
        }
        if ($this->defectOutTypeModal) {
            $defectOutQuery->where("output_defects.defect_type_id", $this->defectOutTypeModal);
        }

        if ($this->defectOutQtyModal > 0) {
            $defectOut = $defectOutQuery->
                orderBy("master_plan.sewing_line")->
                orderBy("master_plan.id_ws")->
                orderBy("master_plan.color")->
                orderBy("output_defect_types.defect_type")->
                orderBy("output_defects.so_det_id")->
                limit($this->defectOutQtyModal)->
                pluck("id");

            DefectInOutModel::whereIn("id", $defectOut)->update([
                "status" => "reworked",
                "updated_at" => Carbon::now(),
                "reworked_at" => Carbon::now()
            ]);

            if (count($defectOut) > 0) {
                $this->emit('alert', 'success', count($defectOut)." DEFECT berhasil keluar dari '".Auth::user()->Groupp."'");
            } else {
                $this->emit('alert', 'warning', "DEFECT gagal keluar dari '".Auth::user()->Groupp."'");
            }
        } else {
            $this->emit('alert', 'warning', "Qty DEFECT OUT 0");
        }

        $this->emit('hideModal', 'defectOut');
    }

    public function showDefectAreaImage($productTypeImage, $x, $y)
    {
        $this->productTypeImage = $productTypeImage;
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;

        $this->emit('showDefectAreaImage', $this->productTypeImage, $this->defectPositionX, $this->defectPositionY);
    }

    public function hideDefectAreaImage()
    {
        $this->productTypeImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    public function render()
    {
        $this->loadingMasterPlan = false;

        $this->lines = UserPassword::where("Groupp", "SEWING")->orderBy("line_id", "asc")->get();

        if ($this->defectInOutputType == 'packing') {
            $defectInQuery = DefectPacking::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects_packing.defect_type_id,
                output_defect_types.defect_type,
                output_defects_packing.so_det_id,
                output_defects_packing.updated_at as defect_time,
                so_det.size,
                'packing' output_type,
                COUNT(output_defects_packing.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects_packing.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects_packing.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects_packing.defect_type_id", $this->defectInSelectedType);
            }
            $defectIn = $defectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects_packing.so_det_id");
        } else {
            $defectInQuery = Defect::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects.defect_type_id,
                output_defect_types.defect_type,
                output_defects.so_det_id,
                output_defects.updated_at as defect_time,
                so_det.size,
                'qc' output_type,
                COUNT(output_defects.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_defects.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            // if ($this->defectInDate) {
            //     $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            // }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects.defect_type_id", $this->defectInSelectedType);
            }
            $defectIn = $defectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id");
        }

        $defectInTotal = $defectIn->get()->sum("defect_qty");

        $defectInList = $defectIn->orderBy("sewing_line")->
            orderBy("output_defects.updated_at", "desc")->
            orderBy("id_ws")->
            orderBy("color")->
            orderBy("defect_type")->
            orderBy("so_det_id")->
            orderBy("output_type")->
            paginate($this->defectInShowPage, ['*'], 'defectInPage');

        $defectOutQuery = DefectInOutModel::selectRaw("
            master_plan.id master_plan_id,
            master_plan.id_ws,
            master_plan.sewing_line,
            act_costing.kpno as ws,
            act_costing.styleno as style,
            master_plan.color as color,
            output_defects.defect_type_id,
            output_defect_types.defect_type,
            output_defects.so_det_id,
            output_defect_in_out.output_type,
            output_defect_in_out.updated_at as defect_time,
            so_det.size,
            COUNT(output_defect_in_out.id) defect_qty
        ")->
        leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        where("output_defect_types.allocation", Auth::user()->Groupp)->
        where("output_defect_in_out.status", "defect")->
        where("output_defect_in_out.output_type", $this->defectOutOutputType)->
        where("output_defect_in_out.type", Auth::user()->Groupp);
        if ($this->defectOutSearch) {
            $defectOutQuery->whereRaw("(
                master_plan.tgl_plan LIKE '%".$this->defectOutSearch."%' OR
                master_plan.sewing_line LIKE '%".$this->defectOutSearch."%' OR
                act_costing.kpno LIKE '%".$this->defectOutSearch."%' OR
                act_costing.styleno LIKE '%".$this->defectOutSearch."%' OR
                master_plan.color LIKE '%".$this->defectOutSearch."%' OR
                output_defect_types.defect_type LIKE '%".$this->defectOutSearch."%' OR
                so_det.size LIKE '%".$this->defectOutSearch."%'
            )");
        }
        // if ($this->defectOutDate) {
        //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$this->defectOutDate." 00:00:00", $this->defectOutDate." 23:59:59"]);
        // }
        if ($this->defectOutLine) {
            $defectInQuery->where("master_plan.sewing_line", $this->defectOutLine);
        }
        if ($this->defectOutSelectedMasterPlan) {
            $defectOutQuery->where("master_plan.id", $this->defectOutSelectedMasterPlan);
        }
        if ($this->defectOutSelectedSize) {
            $defectOutQuery->where("output_defects.so_det_id", $this->defectOutSelectedSize);
        }
        if ($this->defectOutSelectedType) {
            $defectOutQuery->where("output_defects.defect_type_id", $this->defectOutSelectedType);
        }

        $defectOutTotal = $defectOutQuery->get()->sum("defect_qty");

        $defectOutList = $defectOutQuery->
            groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id", "output_defect_in_out.output_type")->
            orderBy("output_defect_in_out.updated_at", "desc")->
            orderBy("master_plan.sewing_line")->
            orderBy("master_plan.id_ws")->
            orderBy("master_plan.color")->
            orderBy("output_defect_types.defect_type")->
            orderBy("output_defects.so_det_id")->
            paginate($this->defectOutShowPage, ['*'], 'defectOutPage');

        // All Defect
        $defectInOutQuery = DefectInOutModel::
            where("type", Auth::user()->Groupp)->
            whereBetween("updated_at", [date('Y-m-d', strtotime($this->date.' -7 days'))." 00:00:00", $this->date." 23:59:59"]);

        $defectInOutTotal = $defectInOutQuery->get()->count();

        $defectInOutList = $defectInOutQuery->
            orderBy("output_defect_in_out.updated_at", "desc")->
            paginate($this->defectInOutShowPage, ['*'], 'defectInOutPage');

        return view('livewire.defect-in-out', ["defectInList" => $defectInList, "defectOutList" => $defectOutList, "defectInOutList" => $defectInOutList, "totalDefectIn" => $defectInTotal, "totalDefectOut" => $defectOutTotal, "totalDefectInOut" => $defectInOutTotal]);
    }

    public function refreshComponent()
    {
        $this->emit('loadingStart');
        $this->emit('$refresh');
        $this->emit('loadingEnd');
    }
}
