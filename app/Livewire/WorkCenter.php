<?php

namespace App\Livewire;

use App\Models\TouchLog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Arr;

#[Title("Centros de trabalho - Tecnolanema")]
class WorkCenter extends BaseComponent
{

    public $selectedWorkCenters = "";
    public $selectedInterruptReasons = "";
    public $filteredData = false;
    public $messages = [];
    public $removeIds = [];

    protected $listeners = [
        "echo:workCenter,UpdateWorkCenter" => "refreshWorkCenters",
        "echo:workCenterStatus,RemoveCard" => "removeCard"
    ];

    #[On('filters-applied')]
    public function applyFilters($events) {
        $this->selectedWorkCenters = $events["selectedWorkCenters"] ?? "";
        $this->selectedInterruptReasons = $events["selectedInterruptReasons"] ?? "";
        $this->filteredData = true;
    }

    public function clearFilters() {
        $this->selectedWorkCenters = [];
        $this->selectedInterruptReasons = [];
        $this->filteredData = false;
    }

    public function updateWorkCenter($event) {
        $id = $event['workCenter']['id'];
        $this->interruptedWorkCenters[$id] = $event['workCenter'];
    }

    #[Computed]
    public function interruptedWorkCenters()
    {
        $this->reset(['messages']);
        $query = TouchLog::query()
            ->select([
                DB::raw('u_logtouch.codct + u_logtouch.tabofopstamp AS id'),
                'u_logtouch.u_logtouchstamp',
                'u_logtouch.tipo',
                DB::raw('u_logtouch.datareg + u_logtouch.horareg AS datahora'),
                'u_tabct.u_tabctstamp',
                'u_tabct.codct',
                'u_tabct.desct',
                'u_tabof.u_tabofstamp',
                'u_tabof.numof',
                'u_tabofop.u_tabofopstamp',
                'u_tabofop.numop',
                DB::raw('u_tabofop.descricao AS descop'),
                'u_tabpr.codigo AS codpr',
                'u_tabpr.descricao AS motivo',
                'pe.nome',
                // Nota: Window functions são pesadas. Certifique-se que tem índices em (datareg, horareg)
                DB::raw('ROW_NUMBER() OVER (
                    PARTITION BY u_tabct.u_tabctstamp, u_tabofop.u_tabofopstamp
                    ORDER BY u_logtouch.datareg DESC, u_logtouch.horareg DESC, u_tabof.u_tabofstamp DESC, u_tabofop.u_tabofopstamp
                ) AS rn')
            ])
            ->join("u_tabct", "u_tabct.codct", "=", "u_logtouch.codct")
            ->join("u_tabofop", function($join) {
                $join->on("u_tabofop.u_tabofopstamp", "=", "u_logtouch.tabofopstamp")
                     ->where("u_tabofop.idto", "<>", 5);
            })
            ->join("u_tabof", function($join) {
                $join->on("u_tabof.u_tabofstamp", "=", "u_tabofop.u_tabofstamp")
                     ->where("u_tabof.idto", "<>", 5);
            })
            ->join("u_tabpr", "u_tabpr.u_tabprstamp", "=", "u_logtouch.tabprstamp")
            ->join("pe", "pe.pestamp", "=", "u_logtouch.pestamp")
            ->where("u_tabct.inactivo", 0)
            ->where("u_tabct.noonline", 0);

        // Aplicação dos Filtros
        if (!empty($this->selectedWorkCenters)) {
            $query->where('u_tabct.codct', $this->selectedWorkCenters);
        }

        if (!empty($this->selectedInterruptReasons)) {
            $query->where('u_tabpr.codigo', $this->selectedInterruptReasons);
        }

        $results = $query->get();

        $interruptedOperations = $results->filter(function($item) {
            // Mantém se for rn=1 E se o ID NÃO estiver na lista de removidos
            return $item->rn == 1 && !in_array($item->u_tabofopstamp, $this->removeIds);
        });

        // Retornamos os resultados.
        // Dica: Se forem muitos registos, considere ->paginate(50) em vez de ->get()
        //return $query->get();
        return $interruptedOperations;
    }

    #[On('update-work-center')]
    public function refreshWorkCenters($params = []) {
        if(!empty($params) && isset($params['message'])) {
            $this->messages = [
                "type" => $params["type"],
                "message" => $params["message"]
            ];
        }

        // Limpa a cache da computed property para forçar re-consulta na base de dados
        unset($this->interruptedWorkCenters);
        // O Livewire encarrega-se de renderizar novamente
    }

    public function removeCard(array $eventData) {
        $operationId = $eventData['u_tabofopstamp'] ?? null;

        if(!$operationId) {
            return;
        }


        $this->removeIds[] = $operationId;
    }

    public function render()
    {
        return view('livewire.work-center', [
            'interruptedWorkCenters' => $this->interruptedWorkCenters
        ]);
    }
}
